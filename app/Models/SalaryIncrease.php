<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalaryIncrease extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'employee_contract_id',
        'base_salary_before',
        'base_salary_after',
        'currency',
        'effective_date',
        'authorized_by',
        'reason',
    ];

    protected $casts = [
        'base_salary_before' => 'decimal:2',
        'base_salary_after' => 'decimal:2',
        'effective_date' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(EmployeeContract::class, 'employee_contract_id');
    }

    public function authorizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'authorized_by');
    }

    public function getChangeAmountAttribute(): float
    {
        return (float) $this->base_salary_after - (float) $this->base_salary_before;
    }
}
