<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductDisplaySetting extends Model
{
    protected $table = 'pharmacy_product_display_settings';

    protected $fillable = [
        'tenant_id',
        'display_strategy',
        'products_limit',
    ];

    protected $casts = [
        'products_limit' => 'integer',
    ];

    /**
     * Get the tenant that owns this setting
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get featured products for this tenant (if using custom_selection strategy)
     */
    public function featuredProducts(): HasMany
    {
        return $this->hasMany(FeaturedProduct::class, 'tenant_id', 'tenant_id');
    }

    /**
     * Get or create setting for a tenant
     */
    public static function forTenant(int $tenantId): self
    {
        return self::firstOrCreate(
            ['tenant_id' => $tenantId],
            [
                'display_strategy' => 'fast_moving',
                'products_limit' => 20,
            ]
        );
    }

    /**
     * Check if using custom selection strategy
     */
    public function isCustomSelection(): bool
    {
        return $this->display_strategy === 'custom_selection';
    }
}

