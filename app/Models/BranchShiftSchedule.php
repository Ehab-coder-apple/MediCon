<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BranchShiftSchedule extends Model
{
    protected $fillable = [
        'tenant_id',
        'branch_id',
        'name',
        'start_time',
        'end_time',
        'timezone',
        'duration_minutes',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'duration_minutes' => 'integer',
    ];

    protected static function booted(): void
    {
        // Always keep duration_minutes in sync with start_time/end_time so
        // payroll math never has to re-derive the overnight-wrap logic.
        static::saving(function (BranchShiftSchedule $schedule) {
            if ($schedule->start_time && $schedule->end_time) {
                $schedule->duration_minutes = $schedule->computeDurationMinutes(
                    $schedule->start_time,
                    $schedule->end_time
                );
            }
        });
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Duration of the shift in decimal hours (e.g. 8.5), ready to be
     * multiplied by an hourly payroll rate. Handles overnight shifts
     * (end_time earlier than start_time) by wrapping past midnight.
     */
    public function getDurationHoursAttribute(): float
    {
        return round(($this->duration_minutes ?? 0) / 60, 2);
    }

    /**
     * Resolve start_time/end_time (wall-clock, in this schedule's timezone)
     * onto a specific calendar date as absolute, timezone-aware instants.
     * Used by payroll/attendance code that needs to compare a real
     * check-in/check-out timestamp against the scheduled window.
     *
     * @return array{0: \Carbon\Carbon, 1: \Carbon\Carbon} [start, end]
     */
    public function resolveWindowFor(\Carbon\Carbon $date): array
    {
        $tz = $this->timezone ?: config('app.timezone', 'UTC');

        $start = \Carbon\Carbon::parse($date->toDateString() . ' ' . $this->start_time, $tz);
        $end = \Carbon\Carbon::parse($date->toDateString() . ' ' . $this->end_time, $tz);

        if ($end->lessThanOrEqualTo($start)) {
            // Overnight shift: end_time is on the following calendar day.
            $end->addDay();
        }

        return [$start, $end];
    }

    /**
     * Compute the duration in minutes between two "H:i[:s]" wall-clock
     * times, wrapping past midnight when end is not after start.
     */
    private function computeDurationMinutes(string $start, string $end): int
    {
        $startSeconds = \Carbon\Carbon::parse($start)->secondsSinceMidnight();
        $endSeconds = \Carbon\Carbon::parse($end)->secondsSinceMidnight();

        $diff = $endSeconds - $startSeconds;

        if ($diff <= 0) {
            // Overnight shift.
            $diff += 24 * 3600;
        }

        return (int) round($diff / 60);
    }
}
