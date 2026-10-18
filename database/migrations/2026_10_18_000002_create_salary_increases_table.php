<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * salary_increases is a permanent, append-only audit trail of base-pay
     * changes (raises, demotions, or hourly-rate revisions), independent
     * of employee_contracts. Each row records the before/after base pay,
     * who authorized it, and when it took effect - kept even if the
     * related employee_contract row is later superseded, so HR always has
     * a structural history of compensation changes.
     */
    public function up(): void
    {
        Schema::create('salary_increases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('employee_contract_id')->nullable()->constrained('employee_contracts')->nullOnDelete();

            // Stored generically so this ledger covers both pay bases
            // (hourly_rate before/after, or monthly_salary before/after).
            $table->decimal('base_salary_before', 12, 2);
            $table->decimal('base_salary_after', 12, 2);
            $table->string('currency', 3)->default('IQD');

            $table->date('effective_date');
            $table->foreignId('authorized_by')->constrained('users')->restrictOnDelete();
            $table->text('reason')->nullable();

            $table->timestamps();

            $table->index(['tenant_id', 'user_id', 'effective_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salary_increases');
    }
};
