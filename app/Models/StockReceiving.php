<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockReceiving extends Model
{
    protected $table = 'stock_receivings';

    protected $fillable = [
        'supplier_id',
        'reference_number',
        'received_date',
        'notes',
        'user_id',
        'status',
        'total_items',
        'total_cost',
    ];

    protected $casts = [
        'received_date' => 'date',
        'total_cost' => 'decimal:2',
    ];

    /**
     * Get the supplier that provided this stock
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Get the user who received this stock
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all items in this stock receiving
     */
    public function items(): HasMany
    {
        return $this->hasMany(StockReceivingItem::class);
    }

    /**
     * Calculate and update totals
     */
    public function updateTotals(): void
    {
        // Total items includes both paid and bonus quantities
        $this->total_items = $this->items()->sum(function ($item) {
            return $item->quantity + $item->bonus_quantity;
        });

        // Total cost only includes paid quantities (bonus is free)
        $this->total_cost = $this->items()->sum(function ($item) {
            return $item->quantity * $item->cost_price;
        });

        $this->save();
    }

    /**
     * Get total paid quantity (excluding bonus)
     */
    public function getTotalPaidQuantityAttribute(): int
    {
        return $this->items()->sum('quantity');
    }

    /**
     * Get total bonus quantity
     */
    public function getTotalBonusQuantityAttribute(): int
    {
        return $this->items()->sum('bonus_quantity');
    }

    /**
     * Get total quantity including bonus
     */
    public function getTotalQuantityAttribute(): int
    {
        return $this->total_paid_quantity + $this->total_bonus_quantity;
    }

    /**
     * Get formatted reference number
     */
    public function getFormattedReferenceAttribute(): string
    {
        return $this->reference_number ?: 'SR-' . str_pad($this->id, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Get status badge color
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'pending' => 'yellow',
            'completed' => 'green',
            'cancelled' => 'red',
            default => 'gray'
        };
    }

    /**
     * Get status text
     */
    public function getStatusTextAttribute(): string
    {
        return match($this->status) {
            'pending' => 'Pending',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            default => 'Unknown'
        };
    }
}
