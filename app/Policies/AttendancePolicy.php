<?php

namespace App\Policies;

use App\Models\Attendance;
use App\Models\Role;
use App\Models\User;

class AttendancePolicy
{
    /**
     * Determine whether the user can view any attendance records
     */
    public function viewAny(User $user): bool
    {
        // Admins and HQ HR Managers can view attendance records (HQ HR
        // Manager is a dedicated global role for global employee oversight,
        // see Role::HQ_HR_MANAGER's default permission set).
        return $user->role?->name === 'admin'
            || $user->hasRole(Role::HQ_HR_MANAGER)
            || $user->is_super_admin;
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

        // Admin or HQ HR Manager can view attendance from their tenant
        if ($user->role?->name === 'admin' || $user->hasRole(Role::HQ_HR_MANAGER)) {
            // If attendance has no tenant_id, allow it to be viewed
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

