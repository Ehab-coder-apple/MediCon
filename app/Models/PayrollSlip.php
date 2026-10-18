<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PayrollSlip extends Model
{
    use BelongsToTenant;

    const STATUS_DRAFT = 'draft';
    const STATUS_PENDING_APPROVAL = 'pending_approval';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_PAID = 'paid';

    protected $fillable = [
        'tenant_id',
        'user_id',
        'branch_id',
        'payroll_month',
        'payroll_year',
        'pay_type',
        'currency',
        'base_pay',
        'regular_hours',
        'overtime_hours',
        'overtime_pay',
        'awards_total',
        'deductions_total',
        'absence_deduction',
        'unexcused_absence_days',
        'gross_pay',
        'net_pay',
        'breakdown',
        'status',
        'generated_at',
        'approved_by',
        'approved_at',
        'notes',
    ];

    protected $casts = [
        'base_pay' => 'decimal:2',
        'regular_hours' => 'decimal:2',
        'overtime_hours' => 'decimal:2',
        'overtime_pay' => 'decimal:2',
        'awards_total' => 'decimal:2',
        'deductions_total' => 'decimal:2',
        'absence_deduction' => 'decimal:2',
        'unexcused_absence_days' => 'integer',
        'gross_pay' => 'decimal:2',
        'net_pay' => 'decimal:2',
        'breakdown' => 'array',
        'generated_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function branchAttributions(): HasMany
    {
        return $this->hasMany(PayrollBranchAttribution::class);
    }

    public function isEditable(): bool
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_PENDING_APPROVAL], true);
    }
}
