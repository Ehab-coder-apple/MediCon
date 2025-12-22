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
