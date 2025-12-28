<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;
use App\Traits\BelongsToTenant;

class User extends Authenticatable
{
    use HasApiTokens;

    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;
    use BelongsToTenant;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'role_id',
        'permissions',
        'branch_id',
        'tenant_id',
        'is_active',
        'is_super_admin',
        'setup_completed',
        'created_by',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'is_super_admin' => 'boolean',
            'permissions' => 'array',
        ];
    }

    /**
     * Get the role that belongs to the user
     */
    public function role(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Get the branch that the user is assigned to (legacy single branch).
     */
    public function branch(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get all branches that the user is assigned to (many-to-many).
     */
    public function branches(): BelongsToMany
    {
        return $this->belongsToMany(Branch::class, 'branch_user');
    }

    /**
     * Get all attendance records for this user
     */
    public function attendances(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    /**
     * Get all leave applications for this user
     */
    public function leaves(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Leave::class);
    }

    /**
     * Get all leave approvals by this user
     */
    public function approvedLeaves(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Leave::class, 'approved_by');
    }

    /**
     * Check if user has a specific role
     */
    public function hasRole(string $roleName): bool
    {
        return $this->role && $this->role->name === $roleName;
    }

    /**
     * Check if user has any of the given roles
     */
    public function hasAnyRole(array $roleNames): bool
    {
        return $this->role && in_array($this->role->name, $roleNames);
    }

    /**
     * Check if user has a specific permission
     */
    public function hasPermission(string $permission): bool
    {
        // If the user has any explicit permissions array (including empty),
        // use it as the single source of truth.
        if (is_array($this->permissions)) {
            return in_array($permission, $this->permissions, true);
        }

        // Otherwise, fall back to role-based permissions (legacy behaviour
        // for older users that don't yet use per-user permissions).
        return $this->role && $this->role->hasPermission($permission);
    }

    /**
     * Get user's role display name
     */
    public function getRoleNameAttribute(): string
    {
        return $this->role ? $this->role->display_name : 'No Role';
    }

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->hasRole(Role::ADMIN);
    }

    /**
     * Check if user is pharmacist
     */
    public function isPharmacist(): bool
    {
        return $this->hasRole(Role::PHARMACIST);
    }

    /**
     * Check if user is sales staff
     */
    public function isSalesStaff(): bool
    {
        return $this->hasRole(Role::SALES_STAFF);
    }

    /**
     * Scope for active users
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for users with specific role
     */
    public function scopeWithRole($query, string $roleName)
    {
        return $query->whereHas('role', function ($q) use ($roleName) {
            $q->where('name', $roleName);
        });
    }

    /**
     * Check if user is super admin
     */
    public function isSuperAdmin(): bool
    {
        return $this->is_super_admin === true;
    }

    /**
     * Check if user is tenant admin
     */
    public function isTenantAdmin(): bool
    {
        return $this->hasRole('admin') && $this->tenant_id !== null;
    }

    /**
     * Check if user can manage tenant
     */
    public function canManageTenant(): bool
    {
        return $this->isSuperAdmin() || $this->isTenantAdmin();
    }

    /**
     * Get user's tenant context
     */
    public function getTenantContext(): ?Tenant
    {
        if ($this->isSuperAdmin()) {
            return app('current_tenant');
        }

        return $this->tenant;
    }

    /**
     * Scope to super admins only
     */
    public function scopeSuperAdmins($query)
    {
        return $query->where('is_super_admin', true);
    }

    /**
     * Scope to tenant users only
     */
    public function scopeTenantUsers($query)
    {
        return $query->whereNotNull('tenant_id');
    }

    /**
     * Override the tenant trait boot to exclude super admins
     */
    protected static function bootBelongsToTenant(): void
    {
        // Temporarily disable global scoping to prevent memory issues
        // TODO: Implement proper tenant resolution middleware

        static::creating(function ($model) {
            // Don't auto-assign tenant to super admins
            if ($model->is_super_admin) {
                return;
            }

            // During CLI operations like seeding, there may be no current_tenant binding
            if (empty($model->tenant_id) && app()->bound('current_tenant')) {
                $tenant = app('current_tenant');
                $model->tenant_id = $tenant?->id;
            }
        });
    }
}
