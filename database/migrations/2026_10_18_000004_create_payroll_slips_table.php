<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * payroll_slips is the finalized (or draft) monthly payslip per user,
     * one row per tenant+user+month+year. 'breakdown' stores the full
     * computed line-item detail (regular/overtime hours, per-adjustment
     * entries, absence deduction math, per-branch attribution summary) as
     * JSON, so the HR approval UI can render it without recomputation and
     * so historical payslips remain stable even if payroll rules change
     * later.
     */
    public function up(): void
    {
        Schema::create('payroll_slips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();

            $table->unsignedTinyInteger('payroll_month');
            $table->unsignedSmallInteger('payroll_year');

            $table->enum('pay_type', ['hourly', 'monthly']);
            $table->string('currency', 3)->default('IQD');

            $table->decimal('base_pay', 12, 2)->default(0);
            $table->decimal('regular_hours', 8, 2)->nullable();
            $table->decimal('overtime_hours', 8, 2)->nullable();
            $table->decimal('overtime_pay', 12, 2)->default(0);
            $table->decimal('awards_total', 12, 2)->default(0);
            $table->decimal('deductions_total', 12, 2)->default(0);
            $table->decimal('absence_deduction', 12, 2)->default(0);
            $table->unsignedInteger('unexcused_absence_days')->default(0);
            $table->decimal('gross_pay', 12, 2)->default(0);
            $table->decimal('net_pay', 12, 2)->default(0);

            $table->json('breakdown')->nullable();

            $table->enum('status', ['draft', 'pending_approval', 'approved', 'rejected', 'paid'])
                ->default('draft');
            $table->timestamp('generated_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->unique(['tenant_id', 'user_id', 'payroll_year', 'payroll_month'], 'payroll_slips_unique_period');
            $table->index(['tenant_id', 'payroll_year', 'payroll_month']);
            $table->index(['tenant_id', 'branch_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_slips');
    }
};
