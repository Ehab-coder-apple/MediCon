<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * branch_allocation_overrides tracks temporary HR "float" reassignments
     * of a user to a different branch for a date range (e.g. covering a
     * shortage at another retail branch). Overlap prevention for the same
     * user is enforced at the service/repository level (see
     * BranchAllocationOverrideService::createOverride()) rather than a raw
     * DB constraint, since neither MySQL nor SQLite support a native
     * date-range-overlap constraint; the service guards it with row
     * locking inside a transaction instead.
     */
    public function up(): void
    {
        Schema::create('branch_allocation_overrides', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('target_branch_id')->constrained('branches')->cascadeOnDelete();
            $table->date('start_date');
            $table->date('end_date');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('reason')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'user_id']);
            // Speeds up the overlap-detection lookup (same user, date range).
            $table->index(['user_id', 'start_date', 'end_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('branch_allocation_overrides');
    }
};
