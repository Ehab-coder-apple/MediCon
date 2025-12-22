<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sale extends Model
{
    protected $fillable = [
        'customer_id',
        'user_id',
        'tenant_id',
        'sale_date',
        'total_price',
        'invoice_number',
        'discount_amount',
        'tax_amount',
        'paid_amount',
        'change_amount',
        'payment_method',
        'notes',
        'status',
    ];

    protected $casts = [
        'sale_date' => 'date',
        'total_price' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'change_amount' => 'decimal:2',
    ];

    /**
     * Get the customer that owns this sale
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the user who created this sale
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the tenant that owns this sale
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get all sale items for this sale
     */
    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    /**
     * Get total quantity of items in this sale
     */
    public function getTotalQuantityAttribute(): int
    {
        return $this->saleItems()->sum('quantity');
    }

    /**
     * Get total number of different products in this sale
     */
    public function getTotalProductsAttribute(): int
    {
        return $this->saleItems()->distinct('product_id')->count();
    }

    /**
     * Generate a unique invoice number
     */
    public static function generateInvoiceNumber(): string
    {
        $prefix = 'INV';
        $date = now()->format('Ymd');
        $sequence = str_pad(static::whereDate('created_at', today())->count() + 1, 4, '0', STR_PAD_LEFT);

        return "{$prefix}-{$date}-{$sequence}";
    }

    /**
     * Scope for completed sales
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope for today's sales
     */
    public function scopeToday($query)
    {
        return $query->whereDate('sale_date', today());
    }

    /**
     * Calculate and update total price based on sale items
     */
    public function updateTotalPrice(): void
    {
        $subtotal = $this->saleItems()->sum('total_price');
        $this->total_price = $subtotal - $this->discount_amount + $this->tax_amount;
        $this->save();
    }

    /**
     * Get subtotal before discount and tax
     */
    public function getSubtotalAttribute(): float
    {
        return $this->saleItems()->sum('total_price');
    }

    /**
     * Get net total after discount and tax
     */
    public function getNetTotalAttribute(): float
    {
        return $this->subtotal - $this->discount_amount + $this->tax_amount;
    }
}
