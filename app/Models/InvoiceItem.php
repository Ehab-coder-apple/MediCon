<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model
{
    protected $fillable = [
        'invoice_id',
        'product_id',
        'batch_id',
        'product_name',
        'product_code',
        'product_description',
        'quantity',
        'unit_price',
        'total_price',
        'discount_amount',
        'discount_percentage',
        'tax_amount',
        'tax_percentage',
        'batch_number',
        'expiry_date',
        'dosage_instructions',
        'days_supply',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'tax_percentage' => 'decimal:2',
        'expiry_date' => 'date',
        'days_supply' => 'integer',
    ];

    /**
     * Get the invoice that owns this item
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Get the product for this item
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the batch for this item
     */
    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    /**
     * Calculate total price based on quantity and unit price
     */
    public function calculateTotalPrice(): void
    {
        $subtotal = $this->quantity * $this->unit_price;
        $discountAmount = $this->discount_percentage > 0
            ? ($subtotal * $this->discount_percentage / 100)
            : $this->discount_amount;

        $afterDiscount = $subtotal - $discountAmount;
        $taxAmount = $this->tax_percentage > 0
            ? ($afterDiscount * $this->tax_percentage / 100)
            : $this->tax_amount;

        $this->discount_amount = $discountAmount;
        $this->tax_amount = $taxAmount;
        $this->total_price = $afterDiscount + $taxAmount;
        $this->save();
    }

    /**
     * Get net unit price after discount
     */
    public function getNetUnitPriceAttribute(): float
    {
        if ($this->quantity <= 0) {
            return 0;
        }

        return ($this->total_price - $this->tax_amount) / $this->quantity;
    }

    /**
     * Get subtotal before discount and tax
     */
    public function getSubtotalAttribute(): float
    {
        return $this->quantity * $this->unit_price;
    }

    /**
     * Check if item is from an expired batch
     */
    public function getIsExpiredAttribute(): bool
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }

    /**
     * Get days until expiry
     */
    public function getDaysUntilExpiryAttribute(): int
    {
        if (!$this->expiry_date) {
            return 0;
        }

        return now()->diffInDays($this->expiry_date, false);
    }

    /**
     * Get formatted expiry date
     */
    public function getFormattedExpiryDateAttribute(): ?string
    {
        return $this->expiry_date ? $this->expiry_date->format('M d, Y') : null;
    }
}
