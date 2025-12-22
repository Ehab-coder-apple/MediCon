<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class Invoice extends Model
{
    protected $fillable = [
        'tenant_id',
        'customer_id',
        'user_id',
        'invoice_number',
        'invoice_date',
        'due_date',
        'subtotal',
        'discount_amount',
        'discount_percentage',
        'tax_amount',
        'tax_percentage',
        'total_amount',
        'paid_amount',
        'balance_due',
        'payment_status',
        'payment_method',
        'payment_reference',
        'paid_at',
        'status',
        'type',
        'notes',
        'terms_conditions',
        'prescription_id',
        'customer_details',
        'delivery_method',
        'delivery_address',
        'delivery_fee',
        'delivered_at',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'tax_percentage' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'balance_due' => 'decimal:2',
        'delivery_fee' => 'decimal:2',
        'customer_details' => 'array',
        'paid_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    /**
     * Get the tenant that owns this invoice
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the customer that owns this invoice
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the user who created this invoice
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all invoice items for this invoice
     */
    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    /**
     * Scope for tenant isolation
     */
    public function scopeForTenant(Builder $query, ?int $tenantId = null): Builder
    {
        $tenantId = $tenantId ?? auth()->user()?->tenant_id;

        if (!$tenantId) {
            throw new \Exception('Tenant ID is required for invoice operations');
        }

        return $query->where('tenant_id', $tenantId);
    }

    /**
     * Generate unique invoice number
     */
    public static function generateInvoiceNumber(int $tenantId): string
    {
        $prefix = config('app.invoice_prefix', 'INV');
        $year = date('Y');
        $month = date('m');

        // Get the last invoice number for this tenant, year, and month
        $lastInvoice = static::where('tenant_id', $tenantId)
            ->where('invoice_number', 'like', "{$prefix}-{$year}{$month}-%")
            ->orderBy('invoice_number', 'desc')
            ->first();

        if ($lastInvoice) {
            // Extract the sequence number and increment
            $lastNumber = (int) substr($lastInvoice->invoice_number, -4);
            $sequence = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $sequence = '0001';
        }

        return "{$prefix}-{$year}{$month}-{$sequence}";
    }

    /**
     * Calculate totals based on invoice items
     */
    public function calculateTotals(): void
    {
        $this->subtotal = $this->items()->sum('total_price');
        $this->total_amount = $this->subtotal - $this->discount_amount + $this->tax_amount + $this->delivery_fee;
        $this->balance_due = $this->total_amount - $this->paid_amount;
        $this->save();
    }

    /**
     * Update payment status based on paid amount
     */
    public function updatePaymentStatus(): void
    {
        if ($this->paid_amount <= 0) {
            $this->payment_status = 'unpaid';
        } elseif ($this->paid_amount >= $this->total_amount) {
            $this->payment_status = 'paid';
            $this->paid_at = $this->paid_at ?? now();
        } else {
            $this->payment_status = 'partial';
        }

        // Check if overdue
        if ($this->payment_status !== 'paid' && $this->due_date && $this->due_date->isPast()) {
            $this->payment_status = 'overdue';
        }

        $this->save();
    }

    /**
     * Get total quantity of items in this invoice
     */
    public function getTotalQuantityAttribute(): int
    {
        return $this->items()->sum('quantity');
    }

    /**
     * Get total number of different products in this invoice
     */
    public function getTotalProductsAttribute(): int
    {
        return $this->items()->distinct('product_id')->count();
    }

    /**
     * Check if invoice is overdue
     */
    public function getIsOverdueAttribute(): bool
    {
        return $this->payment_status !== 'paid' &&
               $this->due_date &&
               $this->due_date->isPast();
    }

    /**
     * Get days until due or overdue
     */
    public function getDaysUntilDueAttribute(): int
    {
        if (!$this->due_date) {
            return 0;
        }

        return now()->diffInDays($this->due_date, false);
    }

    /**
     * Get formatted invoice number for display
     */
    public function getFormattedInvoiceNumberAttribute(): string
    {
        return $this->invoice_number;
    }

    /**
     * Get payment status badge color
     */
    public function getPaymentStatusColorAttribute(): string
    {
        return match($this->payment_status) {
            'paid' => 'green',
            'partial' => 'yellow',
            'overdue' => 'red',
            default => 'gray'
        };
    }

    /**
     * Get status badge color
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'paid' => 'green',
            'sent' => 'blue',
            'viewed' => 'purple',
            'cancelled' => 'red',
            'refunded' => 'orange',
            default => 'gray'
        };
    }
}
