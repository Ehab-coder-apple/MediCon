<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * payroll_adjustments is the comprehensive adjustments ledger: every
     * award/bonus, overtime entry, and deduction that feeds into a given
     * user's payslip for a given payroll month/year. Rows are either:
     *   - 'system_calculated' (e.g. hourly overtime auto-derived from
     *     attendances vs branch_shift_schedules by PayrollService), or
     *   - 'manual' (HR-entered awards, deductions, uniform costs,
     *     disciplinary cuts, absence penalties, tax, or monthly-salary
     *     rest-day overtime, which has no generic auto-detection rule).
     *
     * branch_id is the CRITICAL ALLOCATION LEDGER field: it records which
     * branch this specific adjustment should be financially attributed
     * to. For hours/overtime earned while a user was on an active
     * branch_allocation_override, this is set to the override's
     * target_branch_id (not the user's home branch), so branch-level P&L
     * reporting is accurate. branch_allocation_override_id is kept for
     * full traceability back to the override that caused the
     * attribution.
     */
    public function up(): void
    {
        Schema::create('payroll_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained('branches')->restrictOnDelete();
            $table->foreignId('branch_allocation_override_id')->nullable()
                ->constrained('branch_allocation_overrides')->nullOnDelete();

            $table->enum('type', ['award', 'overtime', 'deduction']);
            // Free-form sub-classification, e.g. 'bonus', 'shift_overtime',
            // 'rest_day_overtime', 'uniform', 'disciplinary',
            // 'absence_penalty', 'tax'.
            $table->string('category')->nullable();

            $table->decimal('amount', 12, 2);
            $table->string('currency', 3)->default('IQD');

            // Present only for overtime rows.
            $table->decimal('hours', 8, 2)->nullable();
            $table->decimal('multiplier', 5, 2)->nullable();

            $table->unsignedTinyInteger('payroll_month');
            $table->unsignedSmallInteger('payroll_year');

            $table->enum('source', ['system_calculated', 'manual'])->default('manual');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['tenant_id', 'user_id', 'payroll_year', 'payroll_month']);
            $table->index(['tenant_id', 'branch_id', 'payroll_year', 'payroll_month']);
            $table->index(['type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_adjustments');
    }
};
