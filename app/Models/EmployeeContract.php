<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmployeeContract extends Model
{
    use BelongsToTenant;

    const PAY_TYPE_HOURLY = 'hourly';
    const PAY_TYPE_MONTHLY = 'monthly';

    protected $fillable = [
        'tenant_id',
        'user_id',
        'pay_type',
        'hourly_rate',
        'monthly_salary',
        'currency',
        'effective_date',
        'end_date',
        'is_active',
        'created_by',
        'notes',
    ];

    protected $casts = [
        'hourly_rate' => 'decimal:2',
        'monthly_salary' => 'decimal:2',
        'effective_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function salaryIncreases(): HasMany
    {
        return $this->hasMany(SalaryIncrease::class);
    }

    /**
     * Scope: contracts effective on or before the given date (used to find
     * the active contract for a given payroll period).
     */
    public function scopeEffectiveOn(Builder $query, \Carbon\Carbon $date): Builder
    {
        return $query->whereDate('effective_date', '<=', $date->toDateString())
            ->where(function ($q) use ($date) {
                $q->whereNull('end_date')
                    ->orWhereDate('end_date', '>=', $date->toDateString());
            });
    }

    public function isHourly(): bool
    {
        return $this->pay_type === self::PAY_TYPE_HOURLY;
    }

    public function isMonthly(): bool
    {
        return $this->pay_type === self::PAY_TYPE_MONTHLY;
    }

    /**
     * The base rate relevant to this contract's pay type (hourly_rate or
     * monthly_salary), used generically by SalaryIncrease records.
     */
    public function getBaseRateAttribute(): float
    {
        return (float) ($this->isHourly() ? $this->hourly_rate : $this->monthly_salary);
    }
}
