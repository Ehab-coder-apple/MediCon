<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BranchAllocationOverride extends Model
{
    protected $fillable = [
        'tenant_id',
        'user_id',
        'target_branch_id',
        'start_date',
        'end_date',
        'created_by',
        'reason',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function targetBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'target_branch_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope: overrides active (covering) a given date.
     */
    public function scopeActiveOn(Builder $query, Carbon $date): Builder
    {
        return $query->whereDate('start_date', '<=', $date->toDateString())
            ->whereDate('end_date', '>=', $date->toDateString());
    }

    /**
     * Scope: overrides for a given user whose date range overlaps the
     * given [start, end] range. Standard interval-overlap comparison:
     * existing.start <= new.end AND existing.end >= new.start.
     */
    public function scopeOverlapping(Builder $query, int $userId, Carbon $start, Carbon $end, ?int $excludeId = null): Builder
    {
        $query->where('user_id', $userId)
            ->whereDate('start_date', '<=', $end->toDateString())
            ->whereDate('end_date', '>=', $start->toDateString());

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query;
    }

    public function isActiveOn(Carbon $date): bool
    {
        return $this->start_date->lessThanOrEqualTo($date->startOfDay())
            && $this->end_date->greaterThanOrEqualTo($date->startOfDay());
    }
}
