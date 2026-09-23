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
        'scope',
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

    // Corporate HQ roles (global scope, assigned strictly to HQ branches)
    const HQ_INVENTORY_MANAGER = 'hq_inventory_manager';
    const HQ_HR_MANAGER = 'hq_hr_manager';

    // Role scopes: 'global' roles operate across the whole tenant/HQ,
    // 'branch' roles are locked to their assigned retail branch.
    const SCOPE_GLOBAL = 'global';
    const SCOPE_BRANCH = 'branch';

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
     * Whether this role operates globally across the tenant/HQ (as opposed
     * to being locked to a single retail branch).
     */
    public function isGlobalScope(): bool
    {
        return $this->scope === self::SCOPE_GLOBAL;
    }

    /**
     * Whether this role is locked to a single retail branch.
     */
    public function isBranchScoped(): bool
    {
        return $this->scope === self::SCOPE_BRANCH;
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
            self::HQ_INVENTORY_MANAGER => [
                // Full control over the central/main warehouse, plus global
                // read visibility into every branch's backroom and shelf
                // stock to monitor thresholds, and the ability to initiate
                // stock transfer orders from HQ to branches.
                'manage_central_warehouse',
                'view_all_branches',
                'view_all_branch_stock',
                'initiate_stock_transfers',
                'manage_batches',
                'view_reports',
            ],
            self::HQ_HR_MANAGER => [
                // Global employee records and payroll properties, plus the
                // unique ability to create temporary branch allocation
                // overrides for staff.
                'access_hr',
                'manage_users',
                'view_all_branches',
                'manage_branch_allocations',
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
