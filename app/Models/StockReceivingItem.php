<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockReceivingItem extends Model
{
    protected $table = 'stock_receiving_items';

    protected $fillable = [
        'stock_receiving_id',
        'product_id',
        'batch_id',
        'batch_number',
        'expiry_date',
        'quantity',
        'bonus_quantity',
        'bonus_notes',
        'cost_price',
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'cost_price' => 'decimal:2',
    ];

    /**
     * Get the stock receiving record
     */
    public function stockReceiving(): BelongsTo
    {
        return $this->belongsTo(StockReceiving::class);
    }

    /**
     * Get the product
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the batch
     */
    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    /**
     * Get total cost for this item (only paid quantity, bonus is free)
     */
    public function getTotalCostAttribute(): float
    {
        return $this->quantity * $this->cost_price;
    }

    /**
     * Get total quantity including bonus
     */
    public function getTotalQuantityAttribute(): int
    {
        return $this->quantity + $this->bonus_quantity;
    }

    /**
     * Get bonus percentage
     */
    public function getBonusPercentageAttribute(): float
    {
        if ($this->quantity == 0) {
            return 0;
        }
        return ($this->bonus_quantity / $this->quantity) * 100;
    }

    /**
     * Check if item has bonus quantity
     */
    public function getHasBonusAttribute(): bool
    {
        return $this->bonus_quantity > 0;
    }

    /**
     * Get days until expiry
     */
    public function getDaysUntilExpiryAttribute(): int
    {
        return now()->diffInDays($this->expiry_date, false);
    }

    /**
     * Check if item is expiring soon
     */
    public function getIsExpiringSoonAttribute(): bool
    {
        return $this->expiry_date > now() && $this->expiry_date <= now()->addDays(30);
    }
}
