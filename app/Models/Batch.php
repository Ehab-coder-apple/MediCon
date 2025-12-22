<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Batch extends Model
{
    protected $fillable = [
        'product_id',
        'batch_number',
        'manufacturer',
        'expiry_date',
        'quantity',
        'cost_price',
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'cost_price' => 'decimal:2',
    ];

    /**
     * Get the product that owns this batch
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Check if batch is expired
     */
    public function getIsExpiredAttribute(): bool
    {
        return $this->expiry_date <= now();
    }

    /**
     * Check if batch is expiring soon (within 30 days)
     */
    public function getIsExpiringSoonAttribute(): bool
    {
        return $this->expiry_date > now() && $this->expiry_date <= now()->addDays(30);
    }

    /**
     * Get days until expiry
     */
    public function getDaysUntilExpiryAttribute(): int
    {
        return now()->diffInDays($this->expiry_date, false);
    }

    /**
     * Check if batch is active (not expired and has quantity)
     */
    public function getIsActiveAttribute(): bool
    {
        return !$this->is_expired && $this->quantity > 0;
    }

    /**
     * Scope for active batches
     */
    public function scopeActive($query)
    {
        return $query->where('quantity', '>', 0)
                    ->where('expiry_date', '>', now());
    }

    /**
     * Scope for expired batches
     */
    public function scopeExpired($query)
    {
        return $query->where('expiry_date', '<=', now());
    }

    /**
     * Scope for expiring soon batches
     */
    public function scopeExpiringSoon($query)
    {
        return $query->where('expiry_date', '>', now())
                    ->where('expiry_date', '<=', now()->addDays(30));
    }
}
