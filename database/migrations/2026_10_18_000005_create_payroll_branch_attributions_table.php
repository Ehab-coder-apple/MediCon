<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * payroll_branch_attributions is the per-branch breakdown of a single
     * payroll_slip's pay, recomputed and rewritten every time that slip is
     * (re)generated. This is what makes branch-level P&L auditing
     * queryable with plain SQL (sum amounts by branch_id) instead of
     * having to parse payroll_slips.breakdown JSON - directly implementing
     * the "accrued hours/overtime/bonuses during a temporary allocation
     * must be attributed to the TARGET BRANCH's ledger" requirement.
     */
    public function up(): void
    {
        Schema::create('payroll_branch_attributions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payroll_slip_id')->constrained('payroll_slips')->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();

            $table->decimal('regular_hours', 8, 2)->default(0);
            $table->decimal('overtime_hours', 8, 2)->default(0);
            $table->decimal('amount', 12, 2)->default(0);
            $table->string('currency', 3)->default('IQD');

            $table->timestamps();

            $table->unique(['payroll_slip_id', 'branch_id'], 'payroll_branch_attributions_unique');
            $table->index(['tenant_id', 'branch_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_branch_attributions');
    }
};
