<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shift extends Model
{
    public const STATUS_OPEN = 'open';
    public const STATUS_CLOSED = 'closed';

    protected $fillable = [
        'user_id',
        'start_time',
        'end_time',
        'starting_cash',
        'ending_cash',
        'status',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'starting_cash' => 'decimal:2',
        'ending_cash' => 'decimal:2',
    ];

    /**
     * The pharmacist who owns this shift session.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * All sales completed during this shift.
     */
    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    /**
     * Scope: only open (active) shifts.
     */
    public function scopeOpen($query)
    {
        return $query->where('status', self::STATUS_OPEN);
    }

    /**
     * Get the current open shift for a given user, if any.
     */
    public static function currentFor(?User $user): ?self
    {
        if (! $user) {
            return null;
        }

        return static::query()
            ->where('user_id', $user->id)
            ->where('status', self::STATUS_OPEN)
            ->latest('start_time')
            ->first();
    }

    /**
     * Whether this shift is currently open.
     */
    public function isOpen(): bool
    {
        return $this->status === self::STATUS_OPEN;
    }

    /**
     * Expected system sales for the shift (sum of completed sale totals).
     */
    public function getExpectedSalesAttribute(): float
    {
        return (float) $this->sales()->where('status', 'completed')->sum('total_price');
    }

    /**
     * Net cash counted into the drawer (ending minus starting). Null while open.
     */
    public function getCashCountedAttribute(): ?float
    {
        if ($this->ending_cash === null) {
            return null;
        }

        return (float) $this->ending_cash - (float) $this->starting_cash;
    }

    /**
     * Variance between counted cash and expected sales. Null while open.
     */
    public function getVarianceAttribute(): ?float
    {
        $counted = $this->cash_counted;

        if ($counted === null) {
            return null;
        }

        return $counted - $this->expected_sales;
    }
}
