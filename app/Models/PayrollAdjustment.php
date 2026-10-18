<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollAdjustment extends Model
{
    use BelongsToTenant;

    const TYPE_AWARD = 'award';
    const TYPE_OVERTIME = 'overtime';
    const TYPE_DEDUCTION = 'deduction';

    const SOURCE_SYSTEM = 'system_calculated';
    const SOURCE_MANUAL = 'manual';

    protected $fillable = [
        'tenant_id',
        'user_id',
        'branch_id',
        'branch_allocation_override_id',
        'type',
        'category',
        'amount',
        'currency',
        'hours',
        'multiplier',
        'payroll_month',
        'payroll_year',
        'source',
        'created_by',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'hours' => 'decimal:2',
        'multiplier' => 'decimal:2',
        'payroll_month' => 'integer',
        'payroll_year' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function allocationOverride(): BelongsTo
    {
        return $this->belongsTo(BranchAllocationOverride::class, 'branch_allocation_override_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeForPeriod(Builder $query, int $month, int $year): Builder
    {
        return $query->where('payroll_month', $month)->where('payroll_year', $year);
    }

    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }
}
