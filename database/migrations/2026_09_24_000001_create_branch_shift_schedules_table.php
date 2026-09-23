<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * branch_shift_schedules tracks a branch's recurring local store rota
     * (e.g. "Morning Rota" 09:00-18:00). It is intentionally isolated from
     * the existing 'shifts' table, which tracks a single cash-drawer
     * clock-in/out session tied to POS sales - a different concept.
     *
     * A 'timezone' column is included (defaulting to the app timezone) so
     * start_time/end_time can always be resolved to an absolute instant
     * for a given calendar date, and 'duration_minutes' is stored
     * explicitly as a plain integer range so future hourly-vs-monthly
     * payroll math has a ready-made decimal-hours figure without having to
     * re-derive it (and re-handle overnight wraparound) at query time.
     */
    public function up(): void
    {
        Schema::create('branch_shift_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->string('name');
            $table->time('start_time');
            $table->time('end_time');
            $table->string('timezone')->default(config('app.timezone', 'UTC'));
            // Explicit decimal-ready duration in minutes, precomputed at
            // save time (see BranchShiftSchedule model), so payroll queries
            // never need to re-derive overnight-wrap logic themselves.
            $table->unsignedInteger('duration_minutes');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['tenant_id', 'branch_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('branch_shift_schedules');
    }
};
