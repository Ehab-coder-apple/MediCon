<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseReturnItem extends Model
{
    protected $fillable = [
        'purchase_return_id',
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
        'unit_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
    ];

    /**
     * Get the purchase return that owns this item
     */
    public function purchaseReturn(): BelongsTo
    {
        return $this->belongsTo(PurchaseReturn::class);
    }

    /**
     * Get the product for this return item
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the batch for this return item
     */
    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    /**
     * Calculate total cost based on quantity and unit cost
     */
    public function calculateTotalCost(): void
    {
        $this->total_cost = $this->quantity * $this->unit_cost;
    }

    /**
     * Boot method to automatically calculate total cost
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($item) {
            $item->calculateTotalCost();
        });

        static::saved(function ($item) {
            // Update the purchase return totals when item is saved
            $item->purchaseReturn->updateTotals();
        });

        static::deleted(function ($item) {
            // Update the purchase return totals when item is deleted
            $item->purchaseReturn->updateTotals();
        });
    }
}

