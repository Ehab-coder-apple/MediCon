<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryReturnItem extends Model
{
    protected $fillable = [
        'inventory_return_id',
        'product_id',
        'batch_id',
        'batch_number',
        'quantity',
        'unit_cost',
        'total_cost',
        'reason',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
    ];

    public function inventoryReturn(): BelongsTo
    {
        return $this->belongsTo(InventoryReturn::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    /**
     * Boot method to handle automatic calculations
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($item) {
            // Calculate total cost
            $item->total_cost = $item->quantity * $item->unit_cost;
        });

        static::saved(function ($item) {
            // Update parent totals
            $item->inventoryReturn->updateTotals();
        });

        static::deleted(function ($item) {
            // Update parent totals after deletion
            $item->inventoryReturn->updateTotals();
        });
    }

    /**
     * Calculate total cost
     */
    public function calculateTotalCost(): decimal
    {
        return $this->quantity * $this->unit_cost;
    }
}

