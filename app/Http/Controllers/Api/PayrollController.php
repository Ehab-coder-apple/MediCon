<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\EmployeeContract;
use App\Models\PayrollAdjustment;
use App\Models\PayrollSlip;
use App\Models\SalaryIncrease;
use App\Models\User;
use App\Services\PayrollService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PayrollController extends Controller
{
    public function __construct(private readonly PayrollService $payrollService)
    {
    }

    // ------------------------------------------------------------------
    // Employee contracts (pay basis: hourly or monthly)
    // ------------------------------------------------------------------

    /**
     * List contracts for the tenant, optionally filtered by user.
     */
    public function contracts(Request $request): JsonResponse
    {
        $this->authorize('manage-payroll');

        $tenantId = $request->user()->tenant_id;

        $contracts = EmployeeContract::where('tenant_id', $tenantId)
            ->with('user:id,name,branch_id')
            ->when($request->filled('user_id'), fn ($q) => $q->where('user_id', $request->integer('user_id')))
            ->orderByDesc('effective_date')
            ->paginate(20);

        return response()->json($contracts);
    }

    /**
     * Create a new employee contract. If the user already has an active
     * contract of the same pay type, it is closed out (end_date set the
     * day before the new one starts) and a salary_increases ledger row is
     * written recording the structural base-pay change.
     */
    public function storeContract(Request $request): JsonResponse
    {
        $this->authorize('manage-payroll');

        $tenantId = $request->user()->tenant_id;

        $validated = $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'pay_type' => 'required|in:hourly,monthly',
            'hourly_rate' => 'required_if:pay_type,hourly|nullable|numeric|min:0',
            'monthly_salary' => 'required_if:pay_type,monthly|nullable|numeric|min:0',
            'currency' => 'nullable|string|size:3',
            'effective_date' => 'required|date',
            'reason' => 'nullable|string|max:255',
        ]);

        $user = User::findOrFail($validated['user_id']);

        if ($user->tenant_id !== $tenantId) {
            abort(403, 'You can only manage contracts within your own organization.');
        }

        $currency = $validated['currency'] ?? 'IQD';
        $effectiveDate = Carbon::parse($validated['effective_date']);
        $newRate = $validated['pay_type'] === 'hourly' ? (float) $validated['hourly_rate'] : (float) $validated['monthly_salary'];

        $contract = DB::transaction(function () use ($request, $tenantId, $user, $validated, $currency, $effectiveDate, $newRate) {
            $previousContract = EmployeeContract::where('tenant_id', $tenantId)
                ->where('user_id', $user->id)
                ->where('is_active', true)
                ->orderByDesc('effective_date')
                ->first();

            if ($previousContract) {
                $previousContract->update([
                    'is_active' => false,
                    'end_date' => $effectiveDate->copy()->subDay(),
                ]);
            }

            $contract = EmployeeContract::create([
                'tenant_id' => $tenantId,
                'user_id' => $user->id,
                'pay_type' => $validated['pay_type'],
                'hourly_rate' => $validated['pay_type'] === 'hourly' ? $validated['hourly_rate'] : null,
                'monthly_salary' => $validated['pay_type'] === 'monthly' ? $validated['monthly_salary'] : null,
                'currency' => $currency,
                'effective_date' => $effectiveDate,
                'is_active' => true,
                'created_by' => $request->user()->id,
                'notes' => $validated['reason'] ?? null,
            ]);

            // Permanent structural history ledger, per spec, regardless of
            // whether this is the user's first contract (before = 0) or a
            // raise/revision of an existing one.
            $previousRate = $previousContract ? $previousContract->base_rate : 0.0;

            SalaryIncrease::create([
                'tenant_id' => $tenantId,
                'user_id' => $user->id,
                'employee_contract_id' => $contract->id,
                'base_salary_before' => $previousRate,
                'base_salary_after' => $newRate,
                'currency' => $currency,
                'effective_date' => $effectiveDate,
                'authorized_by' => $request->user()->id,
                'reason' => $validated['reason'] ?? null,
            ]);

            return $contract;
        });

        return response()->json($contract->load('user:id,name'), 201);
    }

    /**
     * Permanent base-pay change history for a user (salary_increases).
     */
    public function salaryIncreases(Request $request, int $userId): JsonResponse
    {
        $this->authorize('manage-payroll');

        $tenantId = $request->user()->tenant_id;

        $user = User::where('tenant_id', $tenantId)->findOrFail($userId);

        $increases = SalaryIncrease::where('tenant_id', $tenantId)
            ->where('user_id', $user->id)
            ->with('authorizer:id,name')
            ->orderByDesc('effective_date')
            ->get();

        return response()->json($increases);
    }

    // ------------------------------------------------------------------
    // Adjustments ledger (awards, overtime, deductions)
    // ------------------------------------------------------------------

    /**
     * List adjustments for a given payroll period, optionally filtered by
     * user/branch/type.
     */
    public function adjustments(Request $request): JsonResponse
    {
        $this->authorize('manage-payroll');

        $tenantId = $request->user()->tenant_id;

        $validated = $request->validate([
            'month' => 'required|integer|between:1,12',
            'year' => 'required|integer|min:2000|max:2100',
            'user_id' => 'nullable|integer|exists:users,id',
            'branch_id' => 'nullable|integer|exists:branches,id',
            'type' => 'nullable|in:award,overtime,deduction',
        ]);

        $adjustments = PayrollAdjustment::where('tenant_id', $tenantId)
            ->forPeriod($validated['month'], $validated['year'])
            ->when($request->filled('user_id'), fn ($q) => $q->where('user_id', $validated['user_id']))
            ->when($request->filled('branch_id'), fn ($q) => $q->where('branch_id', $validated['branch_id']))
            ->when($request->filled('type'), fn ($q) => $q->where('type', $validated['type']))
            ->with(['user:id,name', 'branch:id,name', 'creator:id,name'])
            ->orderByDesc('created_at')
            ->get();

        return response()->json($adjustments);
    }

    /**
     * Create a manual adjustment: an award/bonus, a manually-entered
     * overtime line (e.g. a monthly-salaried employee's rest-day work), or
     * a deduction (uniform, disciplinary, absence penalty, tax, etc).
     *
     * branch_id defaults to whichever branch the employee is currently
     * attributed to (active allocation override's target branch, or their
     * home branch), honoring the allocation ledger rule for manually
     * entered items too.
     */
    public function storeAdjustment(Request $request): JsonResponse
    {
        $this->authorize('manage-payroll');

        $tenantId = $request->user()->tenant_id;

        $validated = $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'branch_id' => 'nullable|integer|exists:branches,id',
            'type' => 'required|in:award,overtime,deduction',
            'category' => 'nullable|string|max:255',
            'amount' => 'required|numeric|min:0',
            'currency' => 'nullable|string|size:3',
            'hours' => 'nullable|numeric|min:0',
            'multiplier' => 'nullable|numeric|min:0',
            'payroll_month' => 'required|integer|between:1,12',
            'payroll_year' => 'required|integer|min:2000|max:2100',
            'notes' => 'nullable|string|max:1000',
        ]);

        $user = User::findOrFail($validated['user_id']);

        if ($user->tenant_id !== $tenantId) {
            abort(403, 'You can only manage payroll for users within your own organization.');
        }

        $branchId = $validated['branch_id'] ?? null;

        if ($branchId) {
            $branch = Branch::findOrFail($branchId);
            if ($branch->tenant_id !== $tenantId) {
                abort(403, 'You can only attribute payroll to branches within your own organization.');
            }
        } else {
            $branchId = $user->branch_id;
        }

        $adjustment = PayrollAdjustment::create([
            'tenant_id' => $tenantId,
            'user_id' => $user->id,
            'branch_id' => $branchId,
            'type' => $validated['type'],
            'category' => $validated['category'] ?? null,
            'amount' => $validated['amount'],
            'currency' => $validated['currency'] ?? 'IQD',
            'hours' => $validated['hours'] ?? null,
            'multiplier' => $validated['multiplier'] ?? null,
            'payroll_month' => $validated['payroll_month'],
            'payroll_year' => $validated['payroll_year'],
            'source' => PayrollAdjustment::SOURCE_MANUAL,
            'created_by' => $request->user()->id,
            'notes' => $validated['notes'] ?? null,
        ]);

        return response()->json($adjustment->load('branch:id,name'), 201);
    }

    /**
     * Remove a manual adjustment. System-calculated rows are managed only
     * by recalculating the payslip (POST .../generate), not deleted
     * directly, to avoid drifting from the attendance data they came from.
     */
    public function destroyAdjustment(Request $request, PayrollAdjustment $adjustment): JsonResponse
    {
        $this->authorize('manage-payroll');

        if ($adjustment->tenant_id !== $request->user()->tenant_id) {
            abort(403, 'Unauthorized access to this payroll adjustment.');
        }

        if ($adjustment->source === PayrollAdjustment::SOURCE_SYSTEM) {
            abort(422, 'System-calculated adjustments cannot be deleted directly; recalculate the payslip instead.');
        }

        $adjustment->delete();

        return response()->json(['message' => 'Payroll adjustment deleted.']);
    }

    // ------------------------------------------------------------------
    // Payroll slips
    // ------------------------------------------------------------------

    /**
     * List payslips for a given period, optionally filtered by branch or
     * status.
     */
    public function slips(Request $request): JsonResponse
    {
        $this->authorize('manage-payroll');

        $tenantId = $request->user()->tenant_id;

        $validated = $request->validate([
            'month' => 'required|integer|between:1,12',
            'year' => 'required|integer|min:2000|max:2100',
            'branch_id' => 'nullable|integer|exists:branches,id',
            'status' => 'nullable|in:draft,pending_approval,approved,rejected,paid',
        ]);

        $slips = PayrollSlip::where('tenant_id', $tenantId)
            ->where('payroll_month', $validated['month'])
            ->where('payroll_year', $validated['year'])
            ->when($request->filled('branch_id'), fn ($q) => $q->where('branch_id', $validated['branch_id']))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $validated['status']))
            ->with('user:id,name,branch_id')
            ->orderBy('user_id')
            ->get();

        return response()->json($slips);
    }

    /**
     * Full breakdown for a single payslip, including per-branch
     * attribution - used by the HR approval detail view.
     */
    public function showSlip(Request $request, PayrollSlip $slip): JsonResponse
    {
        $this->authorize('manage-payroll');

        if ($slip->tenant_id !== $request->user()->tenant_id) {
            abort(403, 'Unauthorized access to this payroll slip.');
        }

        $slip->load(['user:id,name,email,branch_id', 'branch:id,name', 'approver:id,name', 'branchAttributions.branch:id,name']);

        return response()->json($slip);
    }

    /**
     * Generate/recalculate a single user's payslip for a given month.
     */
    public function generate(Request $request): JsonResponse
    {
        $this->authorize('manage-payroll');

        $validated = $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'month' => 'required|integer|between:1,12',
            'year' => 'required|integer|min:2000|max:2100',
        ]);

        $user = User::findOrFail($validated['user_id']);

        if ($user->tenant_id !== $request->user()->tenant_id) {
            abort(403, 'You can only generate payroll for users within your own organization.');
        }

        $slip = $this->payrollService->calculateMonthlyPayslip($user->id, $validated['month'], $validated['year']);

        return response()->json($slip->load(['user:id,name', 'branchAttributions.branch:id,name']), 201);
    }

    /**
     * Generate/recalculate payslips for every active employee with a
     * contract in the tenant for a given month.
     */
    public function generateAll(Request $request): JsonResponse
    {
        $this->authorize('manage-payroll');

        $validated = $request->validate([
            'month' => 'required|integer|between:1,12',
            'year' => 'required|integer|min:2000|max:2100',
        ]);

        $tenantId = $request->user()->tenant_id;

        $userIds = User::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->whereHas('employeeContracts', fn ($q) => $q->where('is_active', true))
            ->pluck('id');

        $results = [];
        $errors = [];

        foreach ($userIds as $userId) {
            try {
                $slip = $this->payrollService->calculateMonthlyPayslip($userId, $validated['month'], $validated['year']);
                $results[] = $slip->id;
            } catch (\Throwable $e) {
                $errors[] = ['user_id' => $userId, 'message' => $e->getMessage()];
            }
        }

        return response()->json([
            'generated' => count($results),
            'payroll_slip_ids' => $results,
            'errors' => $errors,
        ]);
    }

    /**
     * HR approval: mark a pending payslip as approved.
     */
    public function approve(Request $request, PayrollSlip $slip): JsonResponse
    {
        $this->authorize('manage-payroll');

        if ($slip->tenant_id !== $request->user()->tenant_id) {
            abort(403, 'Unauthorized access to this payroll slip.');
        }

        if (!in_array($slip->status, [PayrollSlip::STATUS_DRAFT, PayrollSlip::STATUS_PENDING_APPROVAL, PayrollSlip::STATUS_REJECTED], true)) {
            abort(422, 'Only draft, pending, or rejected payslips can be approved.');
        }

        $slip->update([
            'status' => PayrollSlip::STATUS_APPROVED,
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
        ]);

        return response()->json($slip);
    }

    /**
     * HR approval: reject a pending payslip, with a reason.
     */
    public function reject(Request $request, PayrollSlip $slip): JsonResponse
    {
        $this->authorize('manage-payroll');

        if ($slip->tenant_id !== $request->user()->tenant_id) {
            abort(403, 'Unauthorized access to this payroll slip.');
        }

        $validated = $request->validate([
            'notes' => 'required|string|max:1000',
        ]);

        $slip->update([
            'status' => PayrollSlip::STATUS_REJECTED,
            'notes' => $validated['notes'],
        ]);

        return response()->json($slip);
    }
}
