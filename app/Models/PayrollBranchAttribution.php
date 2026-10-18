<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollBranchAttribution extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'payroll_slip_id',
        'branch_id',
        'regular_hours',
        'overtime_hours',
        'amount',
        'currency',
    ];

    protected $casts = [
        'regular_hours' => 'decimal:2',
        'overtime_hours' => 'decimal:2',
        'amount' => 'decimal:2',
    ];

    public function payrollSlip(): BelongsTo
    {
        return $this->belongsTo(PayrollSlip::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}
