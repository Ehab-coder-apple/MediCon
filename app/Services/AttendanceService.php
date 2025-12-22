<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Branch;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AttendanceService
{
    /**
     * Calculate distance between two GPS coordinates using Haversine formula
     * Returns distance in meters
     */
    public static function calculateDistance(
        float $lat1,
        float $lon1,
        float $lat2,
        float $lon2
    ): float {
        $earthRadius = 6371000; // Earth's radius in meters

        $latFrom = deg2rad($lat1);
        $lonFrom = deg2rad($lon1);
        $latTo = deg2rad($lat2);
        $lonTo = deg2rad($lon2);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $a = sin($latDelta / 2) * sin($latDelta / 2) +
             cos($latFrom) * cos($latTo) *
             sin($lonDelta / 2) * sin($lonDelta / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Check if GPS coordinates are within geofence of a branch
     */
    public static function isWithinGeofence(
        Branch $branch,
        float $latitude,
        float $longitude
    ): array {
        $distance = self::calculateDistance(
            (float)$branch->latitude,
            (float)$branch->longitude,
            $latitude,
            $longitude
        );

        $isWithin = $distance <= $branch->geofence_radius;

        return [
            'is_within' => $isWithin,
            'distance' => $distance,
            'allowed_radius' => $branch->geofence_radius,
        ];
    }

    /**
     * Record check-in for an employee
     */
    public static function checkIn(
        User $user,
        Branch $branch,
        float $latitude,
        float $longitude,
        ?string $deviceInfo = null,
        ?string $notes = null
    ): array {
        // Check if already checked in today
        $existingCheckIn = Attendance::where('user_id', $user->id)
            ->whereDate('attendance_date', today())
            ->where('status', '!=', 'checked_out')
            ->first();

        if ($existingCheckIn) {
	            Log::info('AttendanceService.checkIn: existing check-in found, skipping new one', [
	                'user_id' => $user->id,
	                'branch_id' => $existingCheckIn->branch_id,
	                'attendance_id' => $existingCheckIn->id,
	                'attendance_date' => optional($existingCheckIn->attendance_date)->toDateString(),
	                'status' => $existingCheckIn->status,
	                'source' => 'mobile',
	            ]);
	
            return [
                'success' => false,
                'message' => 'Employee already checked in today',
                'attendance' => $existingCheckIn,
            ];
        }

        // Validate geofence
        $geofenceCheck = self::isWithinGeofence($branch, $latitude, $longitude);

        // Create attendance record
        // Ensure tenant_id is set (required for multi-tenancy)
        $tenantId = $user->tenant_id ?? $branch->tenant_id;

        $attendance = Attendance::create([
            'tenant_id' => $tenantId,
            'user_id' => $user->id,
            'branch_id' => $branch->id,
            'attendance_date' => today(),
            'check_in_time' => now(),
            'check_in_latitude' => $latitude,
            'check_in_longitude' => $longitude,
            'check_in_within_geofence' => $geofenceCheck['is_within'],
            'check_in_distance_meters' => $geofenceCheck['distance'],
            'check_in_device_info' => $deviceInfo,
            'check_in_notes' => $notes,
            'status' => 'checked_in',
        ]);
	
	        Log::info('AttendanceService.checkIn: attendance created', [
	            'user_id' => $user->id,
	            'branch_id' => $branch->id,
	            'attendance_id' => $attendance->id,
	            'attendance_date' => optional($attendance->attendance_date)->toDateString(),
	            'check_in_time' => optional($attendance->check_in_time)->toDateTimeString(),
	            'within_geofence' => $geofenceCheck['is_within'],
	            'distance' => $geofenceCheck['distance'],
	            'tenant_id' => $tenantId,
	            'source' => 'mobile',
	        ]);
	
	        return [
            'success' => true,
            'message' => $geofenceCheck['is_within']
                ? 'Check-in successful'
                : 'Check-in recorded but outside geofence',
            'attendance' => $attendance,
            'geofence_check' => $geofenceCheck,
        ];
    }

    /**
     * Record check-out for an employee
     */
    public static function checkOut(
        User $user,
        float $latitude,
        float $longitude,
        ?string $deviceInfo = null,
        ?string $notes = null
    ): array {
        // Find today's check-in
        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('attendance_date', today())
            ->where('status', 'checked_in')
            ->first();

        if (!$attendance) {
	            Log::warning('AttendanceService.checkOut: no active check-in found for today', [
	                'user_id' => $user->id,
	                'latitude' => $latitude,
	                'longitude' => $longitude,
	                'source' => 'mobile',
	            ]);
	
            return [
                'success' => false,
                'message' => 'No active check-in found for today',
            ];
        }

        // Validate geofence if branch requires it
        $geofenceCheck = null;
        if ($attendance->branch) {
            $geofenceCheck = self::isWithinGeofence(
                $attendance->branch,
                $latitude,
                $longitude
            );
        }

        // Update attendance record
        $attendance->update([
            'check_out_time' => now(),
            'check_out_latitude' => $latitude,
            'check_out_longitude' => $longitude,
            'check_out_within_geofence' => $geofenceCheck['is_within'] ?? true,
            'check_out_distance_meters' => $geofenceCheck['distance'] ?? null,
            'check_out_device_info' => $deviceInfo,
            'check_out_notes' => $notes,
            'status' => 'checked_out',
            'total_minutes_worked' => $attendance->calculateTotalMinutesWorked(),
        ]);
	
	        Log::info('AttendanceService.checkOut: attendance updated', [
	            'user_id' => $user->id,
	            'branch_id' => $attendance->branch_id,
	            'attendance_id' => $attendance->id,
	            'attendance_date' => optional($attendance->attendance_date)->toDateString(),
	            'check_in_time' => optional($attendance->check_in_time)->toDateTimeString(),
	            'check_out_time' => optional($attendance->check_out_time)->toDateTimeString(),
	            'total_minutes_worked' => $attendance->total_minutes_worked,
	            'within_geofence' => $geofenceCheck['is_within'] ?? true,
	            'distance' => $geofenceCheck['distance'] ?? null,
	            'tenant_id' => $attendance->tenant_id,
	            'source' => 'mobile',
	        ]);
	
	        return [
            'success' => true,
            'message' => 'Check-out successful',
            'attendance' => $attendance,
            'geofence_check' => $geofenceCheck,
        ];
    }

    /**
     * Get attendance summary for a user in a date range
     */
    public static function getUserAttendanceSummary(
        User $user,
        ?Carbon $startDate = null,
        ?Carbon $endDate = null
    ): array {
        $query = Attendance::forUser($user->id);

        if ($startDate || $endDate) {
            $query->dateRange($startDate, $endDate);
        }

        $records = $query->get();

        $totalMinutes = $records->sum('total_minutes_worked') ?? 0;
        $totalHours = $totalMinutes / 60;
        $daysPresent = $records->where('status', 'checked_out')->count();
        $daysIncomplete = $records->where('status', 'checked_in')->count();

        return [
            'total_records' => $records->count(),
            'days_present' => $daysPresent,
            'days_incomplete' => $daysIncomplete,
            'total_minutes' => $totalMinutes,
            'total_hours' => round($totalHours, 2),
            'average_hours_per_day' => $daysPresent > 0 ? round($totalHours / $daysPresent, 2) : 0,
            'records' => $records,
        ];
    }
}

