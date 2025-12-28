<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    protected $fillable = [
        'name',
        'display_name',
        'description',
        'permissions',
        'is_active',
    ];

    protected $casts = [
        'permissions' => 'array',
        'is_active' => 'boolean',
    ];

    // Role constants
    const ADMIN = 'admin';
    const PHARMACIST = 'pharmacist';
    const SALES_STAFF = 'sales_staff';
    const WORKER = 'worker';

    /**
     * Get all users with this role
     */
    public function users(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Check if role has a specific permission
     */
    public function hasPermission(string $permission): bool
    {
        return in_array($permission, $this->permissions ?? []);
    }

    /**
     * Get default permissions for each role
     */
    public static function getDefaultPermissions(string $roleName): array
    {
        return match ($roleName) {
            self::ADMIN => [
                'manage_users',
                'manage_inventory',
                'view_reports',
                'manage_sales',
                'manage_system',
                'manage_products',
                'manage_batches',
                'view_all_branches',
                // New high-level permissions
                'access_ai',
                'access_hr',
                'access_marketing',
                'full_admin_access',
            ],
            self::PHARMACIST => [
                'manage_inventory',
                'view_reports',
                'manage_prescriptions',
                'manage_products',
                'manage_batches',
                'view_own_branch',
                'access_ai',
            ],
            self::SALES_STAFF => [
                'manage_sales',
                'view_inventory',
                'view_own_branch',
                'access_ai',
                'access_marketing',
            ],
            self::WORKER => [
                // General staff with limited operational access
                'view_inventory',
                'view_own_branch',
            ],
            default => [],
        };
    }

    /**
     * Scope for active roles
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
