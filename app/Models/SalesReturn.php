<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\BelongsToTenant;

class SalesReturn extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'invoice_id',
        'customer_id',
        'user_id',
        'reference_number',
        'return_date',
        'status',
        'total_items',
        'total_amount',
        'refund_method',
        'notes',
    ];

    protected $casts = [
        'return_date' => 'date',
        'total_amount' => 'decimal:2',
    ];

    /**
     * Get the tenant that owns this return
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the invoice that this return is for
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Get the customer for this return
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the user who created this return
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all items in this return
     */
    public function items(): HasMany
    {
        return $this->hasMany(SalesReturnItem::class);
    }

    /**
     * Generate a unique reference number
     */
    public static function generateReferenceNumber(): string
    {
        $prefix = 'SR';
        $date = now()->format('Ymd');
        $maxAttempts = 50;

        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            $todayCount = static::whereDate('created_at', today())->count();
            $sequence = $todayCount + $attempt;
            $referenceNumber = "{$prefix}-{$date}-" . str_pad($sequence, 4, '0', STR_PAD_LEFT);

            if (!static::where('reference_number', $referenceNumber)->exists()) {
                return $referenceNumber;
            }
        }

        throw new \Exception('Unable to generate unique reference number');
    }

    /**
     * Calculate and update totals based on return items
     */
    public function updateTotals(): void
    {
        $this->total_items = $this->items()->sum('quantity');
        $this->total_amount = $this->items()->sum('total_price');
        $this->save();
    }

    /**
     * Scope for pending returns
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for approved returns
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope for completed returns
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }
}

