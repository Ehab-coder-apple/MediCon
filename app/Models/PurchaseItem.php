<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseItem extends Model
{
    protected $fillable = [
        'purchase_id',
        'product_id',
        'batch_id',
        'quantity',
        'unit_cost',
        'total_cost',
        'expiry_date',
        'batch_number',
    ];

    protected $casts = [
        'unit_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'expiry_date' => 'date',
    ];

    /**
     * Get the purchase that owns this item
     */
    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    /**
     * Get the product for this purchase item
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the batch for this purchase item (if exists)
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

        static::saving(function ($purchaseItem) {
            $purchaseItem->calculateTotalCost();
        });

        static::saved(function ($purchaseItem) {
            // Update the purchase total cost when item is saved
            $purchaseItem->purchase->updateTotalCost();
        });

        static::deleted(function ($purchaseItem) {
            // Update the purchase total cost when item is deleted
            $purchaseItem->purchase->updateTotalCost();
        });
    }

    /**
     * Create or update batch when purchase item is created
     */
    public function createOrUpdateBatch(): ?Batch
    {
        if (!$this->batch_number || !$this->expiry_date) {
            return null;
        }

        // Check if batch already exists
        $batch = Batch::where('product_id', $this->product_id)
            ->where('batch_number', $this->batch_number)
            ->first();

        if ($batch) {
            // Update existing batch quantity
            $batch->quantity += $this->quantity;
            $batch->save();
        } else {
            // Create new batch
            $batch = Batch::create([
                'product_id' => $this->product_id,
                'batch_number' => $this->batch_number,
                'expiry_date' => $this->expiry_date,
                'quantity' => $this->quantity,
                'cost_price' => $this->unit_cost,
            ]);
        }

        // Update the purchase item with the batch ID
        $this->batch_id = $batch->id;
        $this->save();

        return $batch;
    }
}
