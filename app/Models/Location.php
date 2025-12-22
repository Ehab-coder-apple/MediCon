<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Location extends Model
{
    protected $fillable = [
        'zone',
        'cabinet_shelf',
        'row_level',
        'position_side',
        'full_location',
        'description',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Get all products at this location
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Get active products at this location
     */
    public function activeProducts(): HasMany
    {
        return $this->products()->where('is_active', true);
    }

    /**
     * Scope for active locations
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for ordering locations
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('zone')
                    ->orderBy('cabinet_shelf')
                    ->orderBy('row_level')
                    ->orderBy('position_side');
    }

    /**
     * Scope for filtering by zone
     */
    public function scopeByZone($query, string $zone)
    {
        return $query->where('zone', $zone);
    }

    /**
     * Generate full location string
     */
    public function generateFullLocation(): string
    {
        $parts = array_filter([
            $this->zone,
            $this->cabinet_shelf,
            $this->row_level,
            $this->position_side
        ]);

        return implode(' > ', $parts);
    }

    /**
     * Get unique zones
     */
    public static function getZones(): array
    {
        return self::active()
                   ->distinct()
                   ->orderBy('zone')
                   ->pluck('zone')
                   ->toArray();
    }

    /**
     * Get cabinets/shelves for a specific zone
     */
    public static function getCabinetsForZone(string $zone): array
    {
        return self::active()
                   ->where('zone', $zone)
                   ->distinct()
                   ->orderBy('cabinet_shelf')
                   ->pluck('cabinet_shelf')
                   ->filter()
                   ->toArray();
    }

    /**
     * Get rows/levels for a specific zone and cabinet
     */
    public static function getRowsForCabinet(string $zone, string $cabinet): array
    {
        return self::active()
                   ->where('zone', $zone)
                   ->where('cabinet_shelf', $cabinet)
                   ->distinct()
                   ->orderBy('row_level')
                   ->pluck('row_level')
                   ->filter()
                   ->toArray();
    }

    /**
     * Get positions/sides for a specific zone, cabinet, and row
     */
    public static function getPositionsForRow(string $zone, string $cabinet, string $row): array
    {
        return self::active()
                   ->where('zone', $zone)
                   ->where('cabinet_shelf', $cabinet)
                   ->where('row_level', $row)
                   ->distinct()
                   ->orderBy('position_side')
                   ->pluck('position_side')
                   ->filter()
                   ->toArray();
    }

    /**
     * Boot method to auto-generate full_location
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($location) {
            $location->full_location = $location->generateFullLocation();
        });
    }
}
