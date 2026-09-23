<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\BranchAllocationOverride;
use App\Models\User;
use Carbon\Carbon;

class BranchContextService
{
    /**
     * Resolve the branch a user should actually be operating under on a
     * given date: a currently-active temporary HR allocation override
     * takes priority over the user's permanent branch_id assignment.
     *
     * This is the single "switchboard" every branch-sensitive read/write
     * should go through instead of reading $user->branch_id directly, so a
     * temporarily floated worker is transparently routed to the correct
     * branch for POS branding, local inventory warehouse lookups, and
     * attendance geofence matching without those call sites needing to
     * know overrides exist at all.
     */
    public static function getActiveUserBranchContext(User $user, ?Carbon $onDate = null): ?Branch
    {
        $onDate = $onDate ?? Carbon::now();

        $override = BranchAllocationOverride::with('targetBranch')
            ->activeOn($onDate)
            ->where('user_id', $user->id)
            ->orderByDesc('start_date')
            ->first();

        if ($override && $override->targetBranch) {
            return $override->targetBranch;
        }

        return $user->branch;
    }

    /**
     * Convenience wrapper returning just the resolved branch_id, for the
     * common case of plugging straight into a warehouse/tenant-scoping
     * query that only needs the id.
     */
    public static function getActiveUserBranchId(User $user, ?Carbon $onDate = null): ?int
    {
        return self::getActiveUserBranchContext($user, $onDate)?->id;
    }

    /**
     * Whether the given user is currently operating under a temporary HR
     * allocation override (as opposed to their permanent home branch).
     */
    public static function isUnderOverride(User $user, ?Carbon $onDate = null): bool
    {
        $onDate = $onDate ?? Carbon::now();

        return BranchAllocationOverride::activeOn($onDate)
            ->where('user_id', $user->id)
            ->exists();
    }
}
