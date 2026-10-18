<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\BranchAllocationOverride;
use App\Models\BranchShiftSchedule;
use App\Models\EmployeeContract;
use App\Models\Leave;
use App\Models\PayrollAdjustment;
use App\Models\PayrollBranchAttribution;
use App\Models\PayrollSlip;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PayrollService
{
    /**
     * Calculate (and persist) a user's payslip for a given payroll month.
     *
     * High level flow:
     *  1. Resolve the employee_contract active for this period (hourly or
     *     monthly pay basis).
     *  2. Compute base pay:
     *      - hourly: regular hours x rate from verified attendances, while
     *        auto-writing system-calculated overtime (hours beyond the
     *        branch's standard shift window, at 1.5x) into the
     *        payroll_adjustments ledger.
     *      - monthly: flat contracted salary, minus a per-day deduction
     *        for each unexcused absence day in the month.
     *  3. Loop through every payroll_adjustments row for this user/period
     *     (awards, overtime - system or manual, deductions) and fold them
     *     into gross/net pay.
     *  4. Attribute every peso/dinar of pay to a branch: if the user had
     *     an active branch_allocation_override covering a given work
     *     date, that pay is attributed to the override's TARGET branch
     *     instead of the user's home branch (the "allocation ledger
     *     rule"), written to payroll_branch_attributions for P&L auditing.
     *  5. Upsert the payroll_slips row (one per tenant+user+month+year)
     *     with status 'pending_approval', ready for HR review.
     */
    public function calculateMonthlyPayslip(int $userId, int $month, int $year): PayrollSlip
    {
        $user = User::findOrFail($userId);
        $tenantId = $user->tenant_id;

        $periodStart = Carbon::create($year, $month, 1)->startOfMonth();
        $periodEnd = Carbon::create($year, $month, 1)->endOfMonth();

        $contract = $this->resolveActiveContract($user, $periodEnd);

        if (!$contract) {
            throw new \RuntimeException(
                "No active employee contract found for user #{$userId} covering {$periodStart->format('F Y')}."
            );
        }

        return DB::transaction(function () use ($user, $tenantId, $contract, $periodStart, $periodEnd, $month, $year) {
            // Idempotent recompute: wipe this period's system-calculated
            // overtime so re-running the calculation doesn't double it up.
            PayrollAdjustment::where('tenant_id', $tenantId)
                ->where('user_id', $user->id)
                ->forPeriod($month, $year)
                ->where('source', PayrollAdjustment::SOURCE_SYSTEM)
                ->where('type', PayrollAdjustment::TYPE_OVERTIME)
                ->delete();

            $branchAttribution = [];

            $base = $contract->isHourly()
                ? $this->calculateHourlyBasePay($user, $contract, $periodStart, $periodEnd, $month, $year, $branchAttribution)
                : $this->calculateMonthlyBasePay($user, $contract, $periodStart, $periodEnd, $branchAttribution);

            [$awardsTotal, $deductionsTotal, $overtimePay, $overtimeHours, $adjustmentLines] =
                $this->applyLedgerAdjustments($tenantId, $user->id, $month, $year, $branchAttribution);

            $grossPay = round($base['base_pay'] + $overtimePay + $awardsTotal, 2);
            $netPay = round($grossPay - $deductionsTotal - $base['absence_deduction'], 2);

            $breakdown = [
                'contract' => [
                    'id' => $contract->id,
                    'pay_type' => $contract->pay_type,
                    'rate' => $contract->isHourly() ? (float) $contract->hourly_rate : (float) $contract->monthly_salary,
                    'currency' => $contract->currency,
                ],
                'base' => $base,
                'adjustments' => $adjustmentLines,
                'branch_attribution' => $branchAttribution,
            ];

            $slip = PayrollSlip::updateOrCreate(
                [
                    'tenant_id' => $tenantId,
                    'user_id' => $user->id,
                    'payroll_month' => $month,
                    'payroll_year' => $year,
                ],
                [
                    'branch_id' => $user->branch_id,
                    'pay_type' => $contract->pay_type,
                    'currency' => $contract->currency,
                    'base_pay' => $base['base_pay'],
                    'regular_hours' => $base['regular_hours'] ?? null,
                    'overtime_hours' => round($overtimeHours, 2),
                    'overtime_pay' => round($overtimePay, 2),
                    'awards_total' => round($awardsTotal, 2),
                    'deductions_total' => round($deductionsTotal, 2),
                    'absence_deduction' => $base['absence_deduction'],
                    'unexcused_absence_days' => $base['unexcused_absence_days'] ?? 0,
                    'gross_pay' => $grossPay,
                    'net_pay' => $netPay,
                    'breakdown' => $breakdown,
                    'status' => PayrollSlip::STATUS_PENDING_APPROVAL,
                    'generated_at' => now(),
                ]
            );

            $this->writeBranchAttributions($tenantId, $slip, $contract->currency, $branchAttribution);

            return $slip->fresh('branchAttributions');
        });
    }

    /**
     * Finds the employee_contract that governs the given payroll period:
     * the most recent contract whose effective_date is on/before the end
     * of the period and whose end_date (if any) is on/after it.
     */
    private function resolveActiveContract(User $user, Carbon $periodEnd): ?EmployeeContract
    {
        return EmployeeContract::where('tenant_id', $user->tenant_id)
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->effectiveOn($periodEnd)
            ->orderByDesc('effective_date')
            ->first();
    }

    /**
     * The "allocation ledger rule": resolves which branch a given work
     * date's pay should be attributed to. An active branch_allocation_
     * override covering that date always wins (target branch), so hours/
     * overtime/bonuses earned while temporarily reassigned land on the
     * target branch's P&L instead of the employee's home branch.
     */
    private function resolveAttributionBranch(User $user, Carbon $date, ?int $fallbackBranchId = null): int
    {
        $override = BranchAllocationOverride::where('tenant_id', $user->tenant_id)
            ->where('user_id', $user->id)
            ->activeOn($date)
            ->first();

        return $override->target_branch_id ?? $fallbackBranchId ?? $user->branch_id;
    }

    /**
     * Sum of a branch's active branch_shift_schedules' duration_minutes,
     * used as the "standard time window" beyond which hourly work counts
     * as overtime. Falls back to a standard 8-hour day if the branch has
     * no shift schedule configured.
     */
    private function standardDailyMinutesForBranch(int $branchId): int
    {
        $minutes = BranchShiftSchedule::where('branch_id', $branchId)
            ->where('is_active', true)
            ->sum('duration_minutes');

        return $minutes > 0 ? (int) $minutes : 480;
    }

    /**
     * HOURLY RATE BASIS: regular pay = verified clock-in/out hours x rate.
     * Hours beyond the branch's standard daily window are written to the
     * payroll_adjustments ledger as system-calculated overtime at 1.5x,
     * rather than being folded into the returned base figures, so all
     * overtime (system or manual) is aggregated identically downstream.
     */
    private function calculateHourlyBasePay(
        User $user,
        EmployeeContract $contract,
        Carbon $start,
        Carbon $end,
        int $month,
        int $year,
        array &$branchAttribution
    ): array {
        $rate = (float) $contract->hourly_rate;

        $attendances = Attendance::where('tenant_id', $user->tenant_id)
            ->where('user_id', $user->id)
            ->whereBetween('attendance_date', [$start->toDateString(), $end->toDateString()])
            ->where('status', 'checked_out')
            ->whereNotNull('total_minutes_worked')
            ->get();

        $standardMinutesCache = [];
        $regularMinutes = 0;
        $regularPay = 0.0;
        $overtimeMinutesByBranch = [];

        foreach ($attendances as $attendance) {
            $date = Carbon::parse($attendance->attendance_date);
            $branchId = $this->resolveAttributionBranch($user, $date, $attendance->branch_id);
            $standardMinutes = $standardMinutesCache[$branchId]
                ??= $this->standardDailyMinutesForBranch($branchId);

            $worked = (int) $attendance->total_minutes_worked;
            $dayRegular = min($worked, $standardMinutes);
            $dayOvertime = max(0, $worked - $standardMinutes);

            $dayRegularHours = $dayRegular / 60;
            $dayRegularPay = $dayRegularHours * $rate;

            $regularMinutes += $dayRegular;
            $regularPay += $dayRegularPay;

            $branchAttribution[$branchId] ??= ['regular_hours' => 0.0, 'overtime_hours' => 0.0, 'amount' => 0.0];
            $branchAttribution[$branchId]['regular_hours'] += $dayRegularHours;
            $branchAttribution[$branchId]['amount'] += $dayRegularPay;

            if ($dayOvertime > 0) {
                $overtimeMinutesByBranch[$branchId] = ($overtimeMinutesByBranch[$branchId] ?? 0) + $dayOvertime;
            }
        }

        // Write one system-calculated overtime ledger row per branch
        // (rather than per day) to keep the ledger auditable without an
        // explosion of rows; recomputation deletes and rewrites these.
        foreach ($overtimeMinutesByBranch as $branchId => $minutes) {
            $hours = round($minutes / 60, 2);
            $amount = round($hours * $rate * 1.5, 2);

            PayrollAdjustment::create([
                'tenant_id' => $user->tenant_id,
                'user_id' => $user->id,
                'branch_id' => $branchId,
                'type' => PayrollAdjustment::TYPE_OVERTIME,
                'category' => 'shift_overtime',
                'amount' => $amount,
                'currency' => $contract->currency,
                'hours' => $hours,
                'multiplier' => 1.5,
                'payroll_month' => $month,
                'payroll_year' => $year,
                'source' => PayrollAdjustment::SOURCE_SYSTEM,
                'notes' => 'Auto-calculated: hours beyond branch standard shift window.',
            ]);
        }

        return [
            'base_pay' => round($regularPay, 2),
            'regular_hours' => round($regularMinutes / 60, 2),
            'absence_deduction' => 0.0,
            'unexcused_absence_days' => 0,
        ];
    }

    /**
     * MONTHLY SALARY BASIS: flat contracted monthly_salary, minus a daily-
     * rate deduction (monthly_salary / days_in_month) for each unexcused
     * absence - a day with no attendance record and no approved leave
     * covering it. Rest-day overtime for monthly staff has no generic
     * auto-detection rule (there is no rest-day calendar in the schema),
     * so per the spec it is entered manually as a payroll_adjustments row
     * (type=overtime) rather than computed here.
     */
    private function calculateMonthlyBasePay(
        User $user,
        EmployeeContract $contract,
        Carbon $start,
        Carbon $end,
        array &$branchAttribution
    ): array {
        $monthlySalary = (float) $contract->monthly_salary;
        $daysInMonth = $start->daysInMonth;
        $dailyRate = $daysInMonth > 0 ? $monthlySalary / $daysInMonth : 0.0;

        $today = now();
        $lastDay = ($start->year === $today->year && $start->month === $today->month)
            ? $today->day
            : $daysInMonth;

        $presentDates = Attendance::where('tenant_id', $user->tenant_id)
            ->where('user_id', $user->id)
            ->whereBetween('attendance_date', [$start->toDateString(), $end->toDateString()])
            ->pluck('attendance_date')
            ->map(fn ($d) => Carbon::parse($d)->toDateString())
            ->unique();

        $approvedLeaves = Leave::where('tenant_id', $user->tenant_id)
            ->where('user_id', $user->id)
            ->where('status', 'approved')
            ->where('start_date', '<=', $end->toDateString())
            ->where('end_date', '>=', $start->toDateString())
            ->get();

        $leaveDates = collect();
        foreach ($approvedLeaves as $leave) {
            $leaveStart = $leave->start_date->greaterThan($start) ? $leave->start_date->copy() : $start->copy();
            $leaveEnd = $leave->end_date->lessThan($end) ? $leave->end_date->copy() : $end->copy();

            for ($d = $leaveStart->copy(); $d->lte($leaveEnd); $d->addDay()) {
                $leaveDates->push($d->toDateString());
            }
        }

        $unexcusedDays = 0;

        for ($day = 1; $day <= $lastDay; $day++) {
            $date = Carbon::create($start->year, $start->month, $day);
            $dateStr = $date->toDateString();

            $branchId = $this->resolveAttributionBranch($user, $date, $user->branch_id);
            $branchAttribution[$branchId] ??= ['regular_hours' => 0.0, 'overtime_hours' => 0.0, 'amount' => 0.0];

            $isPresent = $presentDates->contains($dateStr);
            $isOnApprovedLeave = $leaveDates->contains($dateStr);

            if (!$isPresent && !$isOnApprovedLeave) {
                $unexcusedDays++;
                continue;
            }

            $branchAttribution[$branchId]['amount'] += $dailyRate;
        }

        return [
            'base_pay' => round($monthlySalary, 2),
            'regular_hours' => null,
            'absence_deduction' => round($unexcusedDays * $dailyRate, 2),
            'unexcused_absence_days' => $unexcusedDays,
            'daily_rate' => round($dailyRate, 2),
        ];
    }

    /**
     * Loops through every payroll_adjustments row for this user/period
     * (awards, overtime, deductions - system or manual) and folds each
     * into running totals plus the per-branch attribution map.
     *
     * @return array{0: float, 1: float, 2: float, 3: float, 4: array}
     *         [awardsTotal, deductionsTotal, overtimePay, overtimeHours, lines]
     */
    private function applyLedgerAdjustments(int $tenantId, int $userId, int $month, int $year, array &$branchAttribution): array
    {
        $adjustments = PayrollAdjustment::where('tenant_id', $tenantId)
            ->where('user_id', $userId)
            ->forPeriod($month, $year)
            ->get();

        $awardsTotal = 0.0;
        $deductionsTotal = 0.0;
        $overtimePay = 0.0;
        $overtimeHours = 0.0;
        $lines = [];

        foreach ($adjustments as $adjustment) {
            $amount = (float) $adjustment->amount;
            $branchId = $adjustment->branch_id;

            $branchAttribution[$branchId] ??= ['regular_hours' => 0.0, 'overtime_hours' => 0.0, 'amount' => 0.0];

            switch ($adjustment->type) {
                case PayrollAdjustment::TYPE_AWARD:
                    $awardsTotal += $amount;
                    $branchAttribution[$branchId]['amount'] += $amount;
                    break;

                case PayrollAdjustment::TYPE_DEDUCTION:
                    $deductionsTotal += $amount;
                    $branchAttribution[$branchId]['amount'] -= $amount;
                    break;

                case PayrollAdjustment::TYPE_OVERTIME:
                    $overtimePay += $amount;
                    $overtimeHours += (float) ($adjustment->hours ?? 0);
                    $branchAttribution[$branchId]['overtime_hours'] += (float) ($adjustment->hours ?? 0);
                    $branchAttribution[$branchId]['amount'] += $amount;
                    break;
            }

            $lines[] = [
                'id' => $adjustment->id,
                'type' => $adjustment->type,
                'category' => $adjustment->category,
                'amount' => $amount,
                'hours' => $adjustment->hours !== null ? (float) $adjustment->hours : null,
                'multiplier' => $adjustment->multiplier !== null ? (float) $adjustment->multiplier : null,
                'branch_id' => $branchId,
                'source' => $adjustment->source,
                'notes' => $adjustment->notes,
            ];
        }

        return [$awardsTotal, $deductionsTotal, $overtimePay, $overtimeHours, $lines];
    }

    /**
     * Rewrites payroll_branch_attributions for a slip from the computed
     * attribution map, so branch-level P&L can be queried with plain SQL.
     */
    private function writeBranchAttributions(int $tenantId, PayrollSlip $slip, string $currency, array $branchAttribution): void
    {
        PayrollBranchAttribution::where('payroll_slip_id', $slip->id)->delete();

        foreach ($branchAttribution as $branchId => $data) {
            if (!$branchId) {
                continue;
            }

            PayrollBranchAttribution::create([
                'tenant_id' => $tenantId,
                'payroll_slip_id' => $slip->id,
                'branch_id' => $branchId,
                'regular_hours' => round($data['regular_hours'], 2),
                'overtime_hours' => round($data['overtime_hours'], 2),
                'amount' => round($data['amount'], 2),
                'currency' => $currency,
            ]);
        }
    }
}
