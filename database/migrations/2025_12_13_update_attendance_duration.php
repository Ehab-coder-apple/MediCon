<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Attendance;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update all existing attendance records that have check-in and check-out times
        // but don't have total_minutes_worked calculated
        $attendances = Attendance::whereNotNull('check_in_time')
            ->whereNotNull('check_out_time')
            ->whereNull('total_minutes_worked')
            ->get();

        foreach ($attendances as $attendance) {
            $duration = $attendance->calculateTotalMinutesWorked();
            if ($duration !== null) {
                $attendance->update(['total_minutes_worked' => $duration]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No need to reverse this data migration
    }
};

