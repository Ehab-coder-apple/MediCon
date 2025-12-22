<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeaturedProduct extends Model
{
    protected $table = 'featured_products';

    protected $fillable = [
        'tenant_id',
        'product_id',
        'display_order',
    ];

    protected $casts = [
        'display_order' => 'integer',
    ];

    /**
     * Get the tenant that owns this featured product
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the product
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Scope to get featured products for a tenant ordered by display order
     */
    public function scopeForTenant($query, int $tenantId)
    {
        return $query->where('tenant_id', $tenantId)
                     ->orderBy('display_order');
    }

    /**
     * Move product up in display order
     */
    public function moveUp(): void
    {
        if ($this->display_order > 0) {
            $this->display_order--;
            $this->save();
        }
    }

    /**
     * Move product down in display order
     */
    public function moveDown(): void
    {
        $this->display_order++;
        $this->save();
    }
}

