<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Attendance;
use App\Models\Branch;
use App\Models\BranchAllocationOverride;
use App\Models\Leave;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HqHrDashboardController extends Controller
{
    /**
     * HQ HR Manager landing page: an overview dashboard summarizing
     * employee headcount, today's attendance/geofence status, pending
     * leave requests, branch staffing levels, and recent HR activity.
     */
    public function index(Request $request): View
    {
        $this->authorize('access-hq-hr-dashboard');

        $tenantId = $request->user()->tenant_id;
        $today = now()->startOfDay();

        // --- 1. Top stat cards ---------------------------------------
        $totalEmployees = User::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->count();

        $checkedInNow = Attendance::where('tenant_id', $tenantId)
            ->whereDate('attendance_date', $today)
            ->where('status', 'checked_in')
            ->count();

        $pendingLeaves = Leave::where('tenant_id', $tenantId)
            ->where('status', 'pending')
            ->count();

        $activeAllocations = BranchAllocationOverride::where('tenant_id', $tenantId)
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->count();

        // --- 2. Today's attendance & geofence snapshot ----------------
        $todaysAttendance = Attendance::where('tenant_id', $tenantId)
            ->whereDate('attendance_date', $today)
            ->with(['user:id,name', 'branch:id,name'])
            ->orderByDesc('check_in_time')
            ->get();

        $geofenceViolationsToday = $todaysAttendance->filter(function ($attendance) {
            return $attendance->check_in_within_geofence === false
                || $attendance->check_out_within_geofence === false;
        })->values();

        $notCheckedInToday = User::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->whereNotIn('id', $todaysAttendance->pluck('user_id')->filter())
            ->with('branch:id,name')
            ->orderBy('name')
            ->get(['id', 'name', 'branch_id']);

        // --- 3. Pending leave requests panel ---------------------------
        $pendingLeaveRequests = Leave::where('tenant_id', $tenantId)
            ->where('status', 'pending')
            ->with(['user:id,name', 'leaveType:id,name'])
            ->orderBy('start_date')
            ->limit(10)
            ->get();

        // --- 4. Branch staffing overview --------------------------------
        $branches = Branch::where('tenant_id', $tenantId)
            ->active()
            ->orderBy('name')
            ->get();

        $headcountsByBranch = User::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->whereNotNull('branch_id')
            ->selectRaw('branch_id, count(*) as total')
            ->groupBy('branch_id')
            ->pluck('total', 'branch_id');

        $activeOverridesToday = BranchAllocationOverride::where('tenant_id', $tenantId)
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->with('user:id,branch_id')
            ->get();

        $outgoingByBranch = $activeOverridesToday->groupBy(fn ($o) => $o->user?->branch_id);
        $incomingByBranch = $activeOverridesToday->groupBy('target_branch_id');

        $branchStaffing = $branches->map(function ($branch) use ($headcountsByBranch, $outgoingByBranch, $incomingByBranch) {
            return [
                'branch' => $branch,
                'headcount' => (int) ($headcountsByBranch[$branch->id] ?? 0),
                'outgoing' => $outgoingByBranch->get($branch->id, collect())->count(),
                'incoming' => $incomingByBranch->get($branch->id, collect())->count(),
            ];
        });

        // --- 5. Recent HR-filtered activity feed ------------------------
        $recentHrActivity = ActivityLog::where('tenant_id', $tenantId)
            ->whereIn('category', ['hr', 'users'])
            ->with('user:id,name')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        return view('hq.hr.dashboard', compact(
            'totalEmployees',
            'checkedInNow',
            'pendingLeaves',
            'activeAllocations',
            'todaysAttendance',
            'geofenceViolationsToday',
            'notCheckedInToday',
            'pendingLeaveRequests',
            'branchStaffing',
            'recentHrActivity'
        ));
    }

    /**
     * Temporary Branch Allocations page: creating, editing, and revoking
     * temporary branch reassignments. Kept as its own clean page, linked
     * from the dashboard's quick actions.
     */
    public function allocations(Request $request): View
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
