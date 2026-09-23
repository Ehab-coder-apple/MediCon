<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\BranchAllocationOverride;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HqHrDashboardController extends Controller
{
    /**
     * HQ HR Manager landing page: the "Temporary Allocation" dashboard for
     * creating, editing, and revoking temporary branch reassignments.
     */
    public function index(Request $request): View
    {
        $this->authorize('access-hq-hr-dashboard');

        $tenantId = $request->user()->tenant_id;

        // Kept lightweight (id/name/branch_id only) since this only feeds
        // the searchable employee dropdown on the allocation form.
        $employees = User::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'branch_id']);

        $branches = Branch::where('tenant_id', $tenantId)
            ->active()
            ->orderBy('name')
            ->get(['id', 'name', 'branch_type']);

        $overrides = BranchAllocationOverride::where('tenant_id', $tenantId)
            ->with(['user:id,name,branch_id', 'user.branch:id,name', 'targetBranch:id,name', 'creator:id,name'])
            ->orderByDesc('start_date')
            ->paginate(15);

        return view('hq.hr.allocations', compact('employees', 'branches', 'overrides'));
    }
}
