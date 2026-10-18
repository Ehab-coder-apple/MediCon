<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * employee_contracts holds each employee's current pay basis (hourly
     * or monthly) and rate. A user can have multiple rows over time (one
     * per rate change); the row with the latest effective_date that is
     * still <= the target payroll period, and whose end_date is null or
     * on/after that period, is the "active" contract for payroll
     * calculation purposes (see PayrollService::resolveActiveContract()).
     *
     * pay_type is driven by role per the business spec:
     *   - 'hourly'  => pharmacist, cashier-type sales_staff paid per hour
     *   - 'monthly' => admin, hq_inventory_manager, hq_hr_manager,
     *                  sales_staff acting as branch managers, worker
     * This table does not enforce that mapping (roles can change), it
     * just stores whichever basis HR assigned to the contract.
     */
    public function up(): void
    {
        Schema::create('employee_contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            $table->enum('pay_type', ['hourly', 'monthly']);
            $table->decimal('hourly_rate', 12, 2)->nullable();
            $table->decimal('monthly_salary', 12, 2)->nullable();
            $table->string('currency', 3)->default('IQD');

            $table->date('effective_date');
            $table->date('end_date')->nullable();
            $table->boolean('is_active')->default(true);

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['tenant_id', 'user_id', 'is_active']);
            $table->index(['tenant_id', 'user_id', 'effective_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_contracts');
    }
};
