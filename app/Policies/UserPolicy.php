<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    /**
     * HQ HR Manager is a dedicated global role for managing employee
     * records across every branch (see Role::HQ_HR_MANAGER's default
     * permission set, which includes 'manage_users'). The role check is a
     * direct grant so it works out of the box, even if a per-user
     * 'permissions' array (which overrides role defaults entirely once
     * set - see User::hasPermission()) was saved without this box checked.
     */
    private function isUserManager(User $user): bool
    {
        return $user->hasRole(Role::HQ_HR_MANAGER) || $user->hasPermission('manage_users');
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $this->isUserManager($user);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $model): bool
    {
        // Admins (and HQ HR Managers) can view all users
        if ($this->isUserManager($user)) {
            return true;
        }

        // Users can view their own profile
        return $user->id === $model->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $this->isUserManager($user);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $model): bool
    {
        // Admins (and HQ HR Managers) can update all users
        if ($this->isUserManager($user)) {
            return true;
        }

        // Users can update their own profile
        return $user->id === $model->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $model): bool
    {
        // Only admins (and HQ HR Managers) can delete users, but not themselves
        return $this->isUserManager($user) && $user->id !== $model->id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, User $model): bool
    {
        return $this->isUserManager($user);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, User $model): bool
    {
        return $this->isUserManager($user) && $user->id !== $model->id;
    }
}
