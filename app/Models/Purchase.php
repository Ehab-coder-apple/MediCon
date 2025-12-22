<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\BelongsToTenant;

class Purchase extends Model
{
    use BelongsToTenant;
    protected $fillable = [
        'supplier_id',
        'user_id',
        'tenant_id',
        'purchase_date',
        'total_cost',
        'reference_number',
        'notes',
        'status',
        'payment_status',
        'payment_method',
        'paid_amount',
        'balance_due',
        'due_date',
        'paid_at',
        'payment_notes',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'total_cost' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'balance_due' => 'decimal:2',
        'due_date' => 'date',
        'paid_at' => 'date',
    ];

    /**
     * Get the tenant that owns this purchase
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the supplier that owns this purchase
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Get the user who created this purchase
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all purchase items for this purchase
     */
    public function purchaseItems(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }

    /**
     * Get total quantity of items in this purchase
     */
    public function getTotalQuantityAttribute(): int
    {
        return $this->purchaseItems()->sum('quantity');
    }

    /**
     * Get total number of different products in this purchase
     */
    public function getTotalProductsAttribute(): int
    {
        return $this->purchaseItems()->distinct('product_id')->count();
    }

    /**
     * Generate a unique reference number
     */
    public static function generateReferenceNumber(): string
    {
        $prefix = 'PO';
        $date = now()->format('Ymd');

        // Use a more robust approach with random component
        $maxAttempts = 50;

        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            // Get count of today's purchases and add attempt number
            $todayCount = static::whereDate('created_at', today())->count();
            $sequence = $todayCount + $attempt;

            $referenceNumber = "{$prefix}-{$date}-" . str_pad($sequence, 4, '0', STR_PAD_LEFT);

            // Check if this reference number is unique
            if (!static::where('reference_number', $referenceNumber)->exists()) {
                return $referenceNumber;
            }
        }

        // Final fallback: use microseconds for absolute uniqueness
        $microtime = (int)(microtime(true) * 1000) % 10000;
        return "{$prefix}-{$date}-" . str_pad($microtime, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Scope for completed purchases
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope for pending purchases
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Calculate and update total cost based on purchase items
     */
    public function updateTotalCost(): void
    {
        $this->total_cost = $this->purchaseItems()->sum('total_cost');

        // Update balance due if payment status is not paid
        if ($this->payment_status !== 'paid') {
            $this->balance_due = $this->total_cost - $this->paid_amount;
        }

        $this->save();
    }

    /**
     * Scope for unpaid purchases
     */
    public function scopeUnpaid($query)
    {
        return $query->where('payment_status', 'unpaid');
    }

    /**
     * Scope for overdue purchases
     */
    public function scopeOverdue($query)
    {
        return $query->where('payment_status', '!=', 'paid')
                    ->where('due_date', '<', now());
    }

    /**
     * Check if purchase is overdue
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
    public function getDaysUntilDueAttribute(): ?int
    {
        if (!$this->due_date || $this->payment_status === 'paid') {
            return null;
        }

        return now()->diffInDays($this->due_date, false);
    }
}
