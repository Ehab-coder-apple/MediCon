<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\BranchAllocationOverride;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

class BranchAllocationOverrideService
{
    /**
     * Create a temporary branch allocation override for a user, guarding
     * against overlapping date ranges for that same user.
     *
     * The guard is implemented at this repository/service level (rather
     * than a raw DB constraint, which neither MySQL nor SQLite support for
     * date-range overlaps) by locking the user's existing override rows
     * inside a transaction before checking for overlap, so two concurrent
     * requests cannot both pass the check and create conflicting overrides.
     */
    public function createOverride(
        User $user,
        Branch $targetBranch,
        Carbon $startDate,
        Carbon $endDate,
        ?string $reason,
        User $createdBy
    ): BranchAllocationOverride {
        if ($endDate->lessThan($startDate)) {
            throw new Exception('The end date must be on or after the start date.');
        }

        if ($user->tenant_id !== $targetBranch->tenant_id) {
            throw new Exception('The target branch must belong to the same organization as the user.');
        }

        return DB::transaction(function () use ($user, $targetBranch, $startDate, $endDate, $reason, $createdBy) {
            // Lock any existing overrides for this user so a concurrent
            // request can't slip an overlapping row in between our check
            // and our insert.
            $hasOverlap = BranchAllocationOverride::lockForUpdate()
                ->overlapping($user->id, $startDate, $endDate)
                ->exists();

            if ($hasOverlap) {
                throw new Exception('This user already has an overlapping branch allocation for the selected date range.');
            }

            $override = BranchAllocationOverride::create([
                'tenant_id' => $user->tenant_id,
                'user_id' => $user->id,
                'target_branch_id' => $targetBranch->id,
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
                'created_by' => $createdBy->id,
                'reason' => $reason,
            ]);

            LoggingService::logAudit('branch_allocation_override_created', BranchAllocationOverride::class, $override->id, [
                'user_id' => $user->id,
                'target_branch_id' => $targetBranch->id,
                'start_date' => $override->start_date->toDateString(),
                'end_date' => $override->end_date->toDateString(),
                'created_by' => $createdBy->id,
            ]);

            return $override;
        });
    }

    /**
     * Update an existing branch allocation override, re-running the same
     * overlap guard as creation (excluding the override's own row) inside
     * a locked transaction.
     */
    public function updateOverride(
        BranchAllocationOverride $override,
        Branch $targetBranch,
        Carbon $startDate,
        Carbon $endDate,
        ?string $reason
    ): BranchAllocationOverride {
        if ($endDate->lessThan($startDate)) {
            throw new Exception('The end date must be on or after the start date.');
        }

        if ($override->tenant_id !== $targetBranch->tenant_id) {
            throw new Exception('The target branch must belong to the same organization as the user.');
        }

        return DB::transaction(function () use ($override, $targetBranch, $startDate, $endDate, $reason) {
            $hasOverlap = BranchAllocationOverride::lockForUpdate()
                ->overlapping($override->user_id, $startDate, $endDate, $override->id)
                ->exists();

            if ($hasOverlap) {
                throw new Exception('This user already has an overlapping branch allocation for the selected date range.');
            }

            $override->update([
                'target_branch_id' => $targetBranch->id,
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
                'reason' => $reason,
            ]);

            LoggingService::logAudit('branch_allocation_override_updated', BranchAllocationOverride::class, $override->id, [
                'user_id' => $override->user_id,
                'target_branch_id' => $targetBranch->id,
                'start_date' => $override->start_date->toDateString(),
                'end_date' => $override->end_date->toDateString(),
            ]);

            return $override->fresh();
        });
    }

    /**
     * Cancel/delete an override before or during its active window.
     */
    public function deleteOverride(BranchAllocationOverride $override): void
    {
        $override->delete();

        LoggingService::logAudit('branch_allocation_override_deleted', BranchAllocationOverride::class, $override->id, [
            'user_id' => $override->user_id,
            'target_branch_id' => $override->target_branch_id,
        ]);
    }
}
