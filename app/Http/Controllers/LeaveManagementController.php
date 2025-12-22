<?php

namespace App\Http\Controllers;

use App\Models\Leave;
use App\Models\LeaveType;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Carbon\Carbon;

class LeaveManagementController extends Controller
{
    /**
     * Display all leave requests
     */
    public function index(Request $request): View
    {
        $this->authorize('access-admin-dashboard');

        $query = Leave::with(['user', 'leaveType', 'approvedBy', 'tenant']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->filled('start_date')) {
            $query->whereDate('start_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('end_date', '<=', $request->end_date);
        }

        // Filter by user
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by leave type
        if ($request->filled('leave_type_id')) {
            $query->where('leave_type_id', $request->leave_type_id);
        }

        $leaves = $query->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        $users = User::where('tenant_id', auth()->user()->tenant_id)->get();
        $leaveTypes = LeaveType::where('tenant_id', auth()->user()->tenant_id)->get();
        $statuses = ['pending', 'approved', 'rejected', 'cancelled'];

        return view('admin.leaves.index', compact('leaves', 'users', 'leaveTypes', 'statuses'));
    }

    /**
     * Show leave details
     */
    public function show(Leave $leave): View
    {
        $this->authorize('access-admin-dashboard');

        $leave->load(['user', 'leaveType', 'approvedBy']);

        return view('admin.leaves.show', compact('leave'));
    }

    /**
     * Approve leave request
     */
    public function approve(Leave $leave, Request $request): RedirectResponse
    {
        $this->authorize('access-admin-dashboard');

        $validated = $request->validate([
            'approval_notes' => 'nullable|string|max:500',
        ]);

        $leave->approve(auth()->id(), $validated['approval_notes'] ?? null);

        return redirect()->route('admin.leaves.show', $leave)
            ->with('success', 'Leave request approved successfully');
    }

    /**
     * Reject leave request
     */
    public function reject(Leave $leave, Request $request): RedirectResponse
    {
        $this->authorize('access-admin-dashboard');

        $validated = $request->validate([
            'approval_notes' => 'required|string|max:500',
        ]);

        $leave->reject(auth()->id(), $validated['approval_notes']);

        return redirect()->route('admin.leaves.show', $leave)
            ->with('success', 'Leave request rejected successfully');
    }

    /**
     * Export leaves to CSV
     */
    public function export(Request $request)
    {
        $this->authorize('access-admin-dashboard');

        $query = Leave::with(['user', 'leaveType']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('start_date')) {
            $query->whereDate('start_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('end_date', '<=', $request->end_date);
        }

        $leaves = $query->get();

        $filename = 'leaves_' . now()->format('Y-m-d_H-i-s') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($leaves) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Employee', 'Leave Type', 'Start Date', 'End Date', 'Days', 'Status', 'Reason']);

            foreach ($leaves as $leave) {
                fputcsv($file, [
                    $leave->user->name,
                    $leave->leaveType->name,
                    $leave->start_date->format('Y-m-d'),
                    $leave->end_date->format('Y-m-d'),
                    $leave->number_of_days,
                    $leave->status,
                    $leave->reason,
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}

