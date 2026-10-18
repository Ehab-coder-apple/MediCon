<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\EmployeeContract;
use App\Models\PayrollAdjustment;
use App\Models\PayrollSlip;
use App\Models\SalaryIncrease;
use App\Models\User;
use App\Services\PayrollService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class HqHrPayrollController extends Controller
{
    public function __construct(private readonly PayrollService $payrollService)
    {
    }

    /**
     * Contracts page: every active employee alongside their current pay
     * basis (hourly/monthly + rate), with a form to set up or revise a
     * contract (which also writes a salary_increases ledger row).
     */
    public function index(Request $request): View
    {
        $this->authorize('manage-payroll');

        $tenantId = $request->user()->tenant_id;

        $employees = User::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->with(['branch:id,name'])
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'branch_id']);

        $activeContracts = EmployeeContract::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->get()
            ->keyBy('user_id');

        $branches = Branch::where('tenant_id', $tenantId)->active()->orderBy('name')->get(['id', 'name']);

        return view('hq.hr.payroll.index', compact('employees', 'activeContracts', 'branches'));
    }

    /**
     * Creates/revises an employee's pay-basis contract. If an active
     * contract already exists it is closed out the day before the new
     * one takes effect, and a salary_increases row records the change.
     */
    public function storeContract(Request $request): RedirectResponse
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

        $user = User::where('tenant_id', $tenantId)->findOrFail($validated['user_id']);

        $currency = $validated['currency'] ?: 'IQD';
        $effectiveDate = Carbon::parse($validated['effective_date']);
        $newRate = $validated['pay_type'] === 'hourly' ? (float) $validated['hourly_rate'] : (float) $validated['monthly_salary'];

        DB::transaction(function () use ($tenantId, $user, $validated, $currency, $effectiveDate, $newRate, $request) {
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

            SalaryIncrease::create([
                'tenant_id' => $tenantId,
                'user_id' => $user->id,
                'employee_contract_id' => $contract->id,
                'base_salary_before' => $previousContract ? $previousContract->base_rate : 0.0,
                'base_salary_after' => $newRate,
                'currency' => $currency,
                'effective_date' => $effectiveDate,
                'authorized_by' => $request->user()->id,
                'reason' => $validated['reason'] ?? null,
            ]);
        });

        return back()->with('success', "Contract saved for {$user->name}.");
    }

    /**
     * Permanent base-pay change history for a single employee.
     */
    public function salaryIncreases(Request $request, User $user): View
    {
        $this->authorize('manage-payroll');

        if ($user->tenant_id !== $request->user()->tenant_id) {
            abort(403);
        }

        $increases = SalaryIncrease::where('tenant_id', $user->tenant_id)
            ->where('user_id', $user->id)
            ->with('authorizer:id,name')
            ->orderByDesc('effective_date')
            ->get();

        return view('hq.hr.payroll.salary-increases', compact('user', 'increases'));
    }

    /**
     * Payslips page: every payslip generated for a given month/year,
     * with a "Generate All" action to (re)calculate payslips for every
     * employee with an active contract.
     */
    public function slips(Request $request): View
    {
        $this->authorize('manage-payroll');

        $tenantId = $request->user()->tenant_id;

        $month = (int) $request->integer('month', now()->month);
        $year = (int) $request->integer('year', now()->year);

        $slips = PayrollSlip::where('tenant_id', $tenantId)
            ->where('payroll_month', $month)
            ->where('payroll_year', $year)
            ->with('user:id,name,branch_id')
            ->orderBy('user_id')
            ->get();

        $employeesWithContracts = User::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->whereHas('employeeContracts', fn ($q) => $q->where('is_active', true))
            ->count();

        return view('hq.hr.payroll.slips', compact('slips', 'month', 'year', 'employeesWithContracts'));
    }

    /**
     * (Re)calculates payslips for every active employee with a contract
     * for the given month/year.
     */
    public function generateAll(Request $request): RedirectResponse
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

        $generated = 0;
        $errors = [];

        foreach ($userIds as $userId) {
            try {
                $this->payrollService->calculateMonthlyPayslip($userId, $validated['month'], $validated['year']);
                $generated++;
            } catch (\Throwable $e) {
                $errors[] = $e->getMessage();
            }
        }

        $message = "Generated {$generated} payslip(s).";
        if (!empty($errors)) {
            $message .= ' Errors: ' . implode(' | ', array_slice($errors, 0, 3));
        }

        return redirect()
            ->route('hq-hr.payroll.slips', ['month' => $validated['month'], 'year' => $validated['year']])
            ->with(empty($errors) ? 'success' : 'warning', $message);
    }

    /**
     * (Re)calculates a single employee's payslip for the given month/year.
     */
    public function generateOne(Request $request): RedirectResponse
    {
        $this->authorize('manage-payroll');

        $validated = $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'month' => 'required|integer|between:1,12',
            'year' => 'required|integer|min:2000|max:2100',
        ]);

        $user = User::where('tenant_id', $request->user()->tenant_id)->findOrFail($validated['user_id']);

        try {
            $slip = $this->payrollService->calculateMonthlyPayslip($user->id, $validated['month'], $validated['year']);
        } catch (\Throwable $e) {
            return redirect()
                ->route('hq-hr.payroll.slips', ['month' => $validated['month'], 'year' => $validated['year']])
                ->with('warning', "Could not generate payslip for {$user->name}: {$e->getMessage()}");
        }

        return redirect()->route('hq-hr.payroll.slips.show', $slip)->with('success', "Payslip generated for {$user->name}.");
    }

    /**
     * Full breakdown for a single payslip: base pay, ledger adjustments,
     * per-branch attribution, and approve/reject actions.
     */
    public function showSlip(Request $request, PayrollSlip $slip): View
    {
        $this->authorize('manage-payroll');

        if ($slip->tenant_id !== $request->user()->tenant_id) {
            abort(403);
        }

        $slip->load(['user:id,name,email,branch_id', 'branch:id,name', 'approver:id,name', 'branchAttributions.branch:id,name']);

        $adjustments = PayrollAdjustment::where('tenant_id', $slip->tenant_id)
            ->where('user_id', $slip->user_id)
            ->forPeriod($slip->payroll_month, $slip->payroll_year)
            ->with(['branch:id,name', 'creator:id,name'])
            ->orderByDesc('created_at')
            ->get();

        $branches = Branch::where('tenant_id', $slip->tenant_id)->active()->orderBy('name')->get(['id', 'name']);

        return view('hq.hr.payroll.show', compact('slip', 'adjustments', 'branches'));
    }

    /**
     * Adds a manual adjustment (award, overtime, or deduction) for the
     * payslip's user/period. branch_id defaults to the user's home branch
     * if left blank.
     */
    public function storeAdjustment(Request $request): RedirectResponse
    {
        $this->authorize('manage-payroll');

        $tenantId = $request->user()->tenant_id;

        $validated = $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'branch_id' => 'nullable|integer|exists:branches,id',
            'type' => 'required|in:award,overtime,deduction',
            'category' => 'nullable|string|max:255',
            'amount' => 'required|numeric|min:0',
            'hours' => 'nullable|numeric|min:0',
            'multiplier' => 'nullable|numeric|min:0',
            'payroll_month' => 'required|integer|between:1,12',
            'payroll_year' => 'required|integer|min:2000|max:2100',
            'notes' => 'nullable|string|max:1000',
            'redirect_slip' => 'nullable|integer|exists:payroll_slips,id',
        ]);

        $user = User::where('tenant_id', $tenantId)->findOrFail($validated['user_id']);
        $branchId = $validated['branch_id'] ?? $user->branch_id;

        if ($branchId) {
            $branch = Branch::findOrFail($branchId);
            if ($branch->tenant_id !== $tenantId) {
                abort(403);
            }
        }

        PayrollAdjustment::create([
            'tenant_id' => $tenantId,
            'user_id' => $user->id,
            'branch_id' => $branchId,
            'type' => $validated['type'],
            'category' => $validated['category'] ?? null,
            'amount' => $validated['amount'],
            'currency' => 'IQD',
            'hours' => $validated['hours'] ?? null,
            'multiplier' => $validated['multiplier'] ?? null,
            'payroll_month' => $validated['payroll_month'],
            'payroll_year' => $validated['payroll_year'],
            'source' => PayrollAdjustment::SOURCE_MANUAL,
            'created_by' => $request->user()->id,
            'notes' => $validated['notes'] ?? null,
        ]);

        if (!empty($validated['redirect_slip'])) {
            return redirect()
                ->route('hq-hr.payroll.slips.show', $validated['redirect_slip'])
                ->with('success', 'Adjustment added. Regenerate the payslip to include it in totals.');
        }

        return back()->with('success', 'Adjustment added. Regenerate the payslip to include it in totals.');
    }

    /**
     * Removes a manual adjustment. System-calculated rows can only be
     * changed by recalculating the payslip.
     */
    public function destroyAdjustment(Request $request, PayrollAdjustment $adjustment): RedirectResponse
    {
        $this->authorize('manage-payroll');

        if ($adjustment->tenant_id !== $request->user()->tenant_id) {
            abort(403);
        }

        if ($adjustment->source === PayrollAdjustment::SOURCE_SYSTEM) {
            return back()->with('warning', 'System-calculated adjustments cannot be deleted directly; recalculate the payslip instead.');
        }

        $adjustment->delete();

        return back()->with('success', 'Adjustment removed. Regenerate the payslip to update totals.');
    }

    public function approveSlip(Request $request, PayrollSlip $slip): RedirectResponse
    {
        $this->authorize('manage-payroll');

        if ($slip->tenant_id !== $request->user()->tenant_id) {
            abort(403);
        }

        $slip->update([
            'status' => PayrollSlip::STATUS_APPROVED,
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Payslip approved.');
    }

    public function rejectSlip(Request $request, PayrollSlip $slip): RedirectResponse
    {
        $this->authorize('manage-payroll');

        if ($slip->tenant_id !== $request->user()->tenant_id) {
            abort(403);
        }

        $validated = $request->validate(['notes' => 'required|string|max:1000']);

        $slip->update([
            'status' => PayrollSlip::STATUS_REJECTED,
            'notes' => $validated['notes'],
        ]);

        return back()->with('warning', 'Payslip rejected.');
    }
}
