<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\BranchAllocationOverride;
use App\Models\User;
use Carbon\Carbon;

class BranchContextService
{
    /**
     * Session key an admin/HQ-manager's chosen "viewing context" branch is
     * stored under (see setViewingBranch()/clearViewingBranch()).
     */
    private const VIEWING_BRANCH_SESSION_KEY = 'active_viewing_branch_id';

    /**
     * Resolve the branch a user should actually be operating under on a
     * given date. Resolution priority (highest first):
     *   1. A global-role user's (admin, hq_inventory_manager, hq_hr_manager)
     *      temporary "viewing context" switch, set via the header context
     *      dropdown / setViewingBranch() - lets them browse dashboards as
     *      if operating from a different branch without touching their
     *      actual branch_id or creating an HR allocation record.
     *   2. A currently-active temporary HR allocation override.
     *   3. The user's permanent branch_id assignment.
     *
     * This is the single "switchboard" every branch-sensitive read/write
     * should go through instead of reading $user->branch_id directly, so a
     * temporarily floated worker (or an admin previewing another branch) is
     * transparently routed to the correct branch for POS branding, local
     * inventory warehouse lookups, and attendance geofence matching without
     * those call sites needing to know overrides exist at all.
     */
    public static function getActiveUserBranchContext(User $user, ?Carbon $onDate = null): ?Branch
    {
        if ($user->isGlobalRole()) {
            $viewingBranch = self::getViewingBranch($user);
            if ($viewingBranch) {
                return $viewingBranch;
            }
        }

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
     * The branch currently stored in session as the user's chosen viewing
     * context, if any, re-validated to still belong to the user's tenant
     * (defends against a stale session surviving a tenant switch).
     */
    public static function getViewingBranch(User $user): ?Branch
    {
        $branchId = session(self::VIEWING_BRANCH_SESSION_KEY);
        if (! $branchId) {
            return null;
        }

        return Branch::where('id', $branchId)
            ->where('tenant_id', $user->tenant_id)
            ->first();
    }

    /**
     * Set (or clear, when $branchId is null) the given user's temporary
     * dashboard viewing-context branch. Only global-role users may use
     * this - see the 'switch-branch-viewing-context' Gate, which the
     * calling controller must authorize before invoking this method.
     *
     * Returns false (and leaves the session untouched) when the user isn't
     * a global role or the requested branch doesn't belong to their tenant.
     */
    public static function setViewingBranch(User $user, ?int $branchId): bool
    {
        if (! $user->isGlobalRole()) {
            return false;
        }

        if ($branchId === null) {
            session()->forget(self::VIEWING_BRANCH_SESSION_KEY);
            return true;
        }

        $branch = Branch::where('id', $branchId)
            ->where('tenant_id', $user->tenant_id)
            ->first();

        if (! $branch) {
            return false;
        }

        session([self::VIEWING_BRANCH_SESSION_KEY => $branch->id]);

        return true;
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
