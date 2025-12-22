<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\BelongsToTenant;
use Carbon\Carbon;

class Attendance extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'branch_id',
        'attendance_date',
        'check_in_time',
        'check_in_latitude',
        'check_in_longitude',
        'check_in_within_geofence',
        'check_in_distance_meters',
        'check_out_time',
        'check_out_latitude',
        'check_out_longitude',
        'check_out_within_geofence',
        'check_out_distance_meters',
        'total_minutes_worked',
        'status',
        'check_in_notes',
        'check_out_notes',
        'check_in_device_info',
        'check_out_device_info',
        'break_start_time',
        'break_end_time',
        'break_duration_minutes',
        'total_break_count',
    ];

    protected $casts = [
        'attendance_date' => 'date',
        'check_in_time' => 'datetime',
        'check_out_time' => 'datetime',
        'break_start_time' => 'datetime',
        'break_end_time' => 'datetime',
        'check_in_latitude' => 'decimal:8',
        'check_in_longitude' => 'decimal:8',
        'check_out_latitude' => 'decimal:8',
        'check_out_longitude' => 'decimal:8',
        'check_in_within_geofence' => 'boolean',
        'check_out_within_geofence' => 'boolean',
        'check_in_distance_meters' => 'float',
        'check_out_distance_meters' => 'float',
        'total_minutes_worked' => 'integer',
        'break_duration_minutes' => 'integer',
        'total_break_count' => 'integer',
    ];

    /**
     * Get the user who has this attendance record
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the branch where attendance was recorded
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the tenant this attendance belongs to
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Calculate total minutes worked
     */
    public function calculateTotalMinutesWorked(): ?int
    {
        if (!$this->check_in_time || !$this->check_out_time) {
            return null;
        }

        return (int) round($this->check_in_time->diffInMinutes($this->check_out_time));
    }

    /**
     * Format total minutes as hours and minutes
     */
    public function getFormattedDurationAttribute(): string
    {
        if (!$this->total_minutes_worked) {
            return 'N/A';
        }

        $hours = intdiv($this->total_minutes_worked, 60);
        $minutes = $this->total_minutes_worked % 60;

        return sprintf('%dh %dm', $hours, $minutes);
    }

    /**
     * Scope to filter by date range
     */
    public function scopeDateRange($query, ?Carbon $startDate, ?Carbon $endDate)
    {
        if ($startDate) {
            $query->whereDate('attendance_date', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('attendance_date', '<=', $endDate);
        }

        return $query;
    }

    /**
     * Scope to filter by user
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to filter by branch
     */
    public function scopeForBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    /**
     * Scope to filter by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for today's attendance
     */
    public function scopeToday($query)
    {
        return $query->whereDate('attendance_date', today());
    }

    /**
     * Check if attendance is complete (both check-in and check-out)
     */
    public function isComplete(): bool
    {
        return $this->check_in_time && $this->check_out_time;
    }

    /**
     * Get status badge color
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'checked_in' => 'blue',
            'checked_out' => 'green',
            'incomplete' => 'yellow',
            'pending' => 'gray',
            default => 'gray'
        };
    }
}
