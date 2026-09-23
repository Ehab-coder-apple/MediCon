<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Traits\BelongsToTenant;

class Branch extends Model
{
    // use BelongsToTenant; // Temporarily disabled for seeding

    /**
     * Branch hierarchy types.
     */
    public const TYPE_HQ = 'HQ';
    public const TYPE_RETAIL_PHARMACY = 'RETAIL_PHARMACY';

    protected $fillable = [
        'name',
        'code',
        'description',
        'address',
        'city',
        'state',
        'country',
        'postal_code',
        'latitude',
        'longitude',
        'geofence_radius',
        'phone',
        'email',
        'manager_name',
        'operating_hours',
        'is_active',
        'requires_geofencing',
        'settings',
        'tenant_id',
        'branch_type',
        'parent_id',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'geofence_radius' => 'integer',
        'is_active' => 'boolean',
        'requires_geofencing' => 'boolean',
        'operating_hours' => 'array',
        'settings' => 'array',
    ];

    /**
     * Get all users assigned to this branch (legacy single branch).
     */
    public function usersLegacy(): HasMany
    {
        return $this->hasMany(User::class, 'branch_id');
    }

    /**
     * Get all users assigned to this branch (many-to-many).
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'branch_user');
    }



    /**
     * Get the tenant this branch belongs to
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the parent HQ branch, if any.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'parent_id');
    }

    /**
     * Get the child retail branches reporting up to this (HQ) branch.
     */
    public function children(): HasMany
    {
        return $this->hasMany(Branch::class, 'parent_id');
    }

    /**
     * Scope: only corporate HQ branches.
     */
    public function scopeHq($query)
    {
        return $query->where('branch_type', self::TYPE_HQ);
    }

    /**
     * Scope: only retail pharmacy branches.
     */
    public function scopeRetail($query)
    {
        return $query->where('branch_type', self::TYPE_RETAIL_PHARMACY);
    }

    /**
     * Whether this branch is a corporate HQ branch.
     */
    public function isHq(): bool
    {
        return $this->branch_type === self::TYPE_HQ;
    }

    /**
     * Whether this branch is a retail pharmacy branch.
     */
    public function isRetail(): bool
    {
        return $this->branch_type === self::TYPE_RETAIL_PHARMACY;
    }

    /**
     * Validate the proposed hierarchy for this branch before it is
     * persisted: an HQ branch may never have a parent, and a parent (when
     * set) must itself be an HQ branch within the same tenant. Returns an
     * error message string on failure, or null when the hierarchy is valid.
     *
     * This is an application-level guard (called from controllers) rather
     * than a DB constraint, since it needs to compare branch_type across
     * rows and is tenant-scoped.
     */
    public function validateHierarchy(?int $parentId, string $branchType, ?int $tenantId = null): ?string
    {
        $tenantId = $tenantId ?? $this->tenant_id;

        if ($branchType === self::TYPE_HQ && $parentId) {
            return 'An HQ branch cannot have a parent branch.';
        }

        if ($parentId) {
            if ($parentId === $this->id) {
                return 'A branch cannot be its own parent.';
            }

            $parent = static::find($parentId);

            if (! $parent) {
                return 'The selected parent branch does not exist.';
            }

            if (! $parent->isHq()) {
                return 'The parent branch must be a corporate HQ branch.';
            }

            if ($tenantId !== null && $parent->tenant_id !== $tenantId) {
                return 'The parent branch must belong to the same organization.';
            }
        }

        return null;
    }

    /**
     * Get the user who created this branch
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this branch
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Scope for active branches
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for branches that require geofencing
     */
    public function scopeRequiresGeofencing($query)
    {
        return $query->where('requires_geofencing', true);
    }

    /**
     * Get full address as a single string
     */
    public function getFullAddressAttribute(): string
    {
        return implode(', ', array_filter([
            $this->address,
            $this->city,
            $this->state,
            $this->postal_code,
            $this->country
        ]));
    }

    /**
     * Check if a given coordinate is within the geofence
     */
    public function isWithinGeofence(float $latitude, float $longitude): bool
    {
        if (!$this->requires_geofencing) {
            return true;
        }

        $distance = $this->calculateDistance($latitude, $longitude);
        return $distance <= $this->geofence_radius;
    }

    /**
     * Calculate distance between two coordinates using Haversine formula
     */
    public function calculateDistance(float $latitude, float $longitude): float
    {
        $earthRadius = 6371000; // Earth's radius in meters

        $latFrom = deg2rad($this->latitude);
        $lonFrom = deg2rad($this->longitude);
        $latTo = deg2rad($latitude);
        $lonTo = deg2rad($longitude);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $a = sin($latDelta / 2) * sin($latDelta / 2) +
             cos($latFrom) * cos($latTo) *
             sin($lonDelta / 2) * sin($lonDelta / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Get default operating hours
     */
    public static function getDefaultOperatingHours(): array
    {
        return [
            'monday' => ['open' => '09:00', 'close' => '18:00', 'closed' => false],
            'tuesday' => ['open' => '09:00', 'close' => '18:00', 'closed' => false],
            'wednesday' => ['open' => '09:00', 'close' => '18:00', 'closed' => false],
            'thursday' => ['open' => '09:00', 'close' => '18:00', 'closed' => false],
            'friday' => ['open' => '09:00', 'close' => '18:00', 'closed' => false],
            'saturday' => ['open' => '09:00', 'close' => '17:00', 'closed' => false],
            'sunday' => ['open' => '10:00', 'close' => '16:00', 'closed' => false],
        ];
    }

    /**
     * Check if branch is currently open
     */
    public function isCurrentlyOpen(): bool
    {
        if (!$this->operating_hours) {
            return true; // Assume open if no hours set
        }

        $now = now();
        $dayOfWeek = strtolower($now->format('l'));
        $currentTime = $now->format('H:i');

        $todayHours = $this->operating_hours[$dayOfWeek] ?? null;

        if (!$todayHours || $todayHours['closed']) {
            return false;
        }

        return $currentTime >= $todayHours['open'] && $currentTime <= $todayHours['close'];
    }
}
