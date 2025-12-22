<?php

namespace App\Policies;

use App\Models\Attendance;
use App\Models\User;

class AttendancePolicy
{
    /**
     * Determine whether the user can view any attendance records
     */
    public function viewAny(User $user): bool
    {
        // Only admins can view attendance records
        return $user->role?->name === 'admin' || $user->is_super_admin;
    }

    /**
     * Determine whether the user can view the attendance record
     */
    public function view(User $user, Attendance $attendance): bool
    {
        // Super admin can view any attendance
        if ($user->is_super_admin) {
            return true;
        }

        // Admin can view attendance from their tenant
        if ($user->role?->name === 'admin') {
            // If attendance has no tenant_id, allow admin to view it
            if (!$attendance->tenant_id) {
                return true;
            }
            return $user->tenant_id === $attendance->tenant_id;
        }

        // Users can view their own attendance
        return $user->id === $attendance->user_id;
    }

    /**
     * Determine whether the user can create attendance records
     */
    public function create(User $user): bool
    {
        // Only system can create attendance records via API
        return false;
    }

    /**
     * Determine whether the user can update the attendance record
     */
    public function update(User $user, Attendance $attendance): bool
    {
        // Only admins can update attendance records
        if ($user->is_super_admin) {
            return true;
        }

        if ($user->role?->name === 'admin') {
            // If attendance has no tenant_id, allow admin to update it
            if (!$attendance->tenant_id) {
                return true;
            }
            return $user->tenant_id === $attendance->tenant_id;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the attendance record
     */
    public function delete(User $user, Attendance $attendance): bool
    {
        // Only super admins can delete attendance records
        return $user->is_super_admin;
    }
}

