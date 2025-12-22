<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Leave;
use App\Models\LeaveType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class LeaveApiController extends Controller
{
    /**
     * Get all leave types
     */
    public function getLeaveTypes()
    {
        $leaveTypes = LeaveType::where('tenant_id', Auth::user()->tenant_id)
            ->active()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $leaveTypes,
        ]);
    }

    /**
     * Apply for leave
     */
    public function applyLeave(Request $request)
    {
        $validated = $request->validate([
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'nullable|string|max:500',
            'is_half_day' => 'boolean',
            'half_day_type' => 'nullable|in:morning,afternoon',
        ]);

        $user = Auth::user();
        $startDate = Carbon::parse($validated['start_date']);
        $endDate = Carbon::parse($validated['end_date']);

        // Calculate number of days
        $numberOfDays = abs($endDate->diffInDays($startDate)) + 1;
        if ($validated['is_half_day'] ?? false) {
            $numberOfDays = 0.5;
        }

        // Check for overlapping leaves
        $overlapping = Leave::where('user_id', $user->id)
            ->where('status', '!=', 'rejected')
            ->where('status', '!=', 'cancelled')
            ->whereBetween('start_date', [$startDate, $endDate])
            ->orWhere(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('end_date', [$startDate, $endDate]);
            })
            ->exists();

        if ($overlapping) {
            return response()->json([
                'success' => false,
                'message' => 'Leave already exists for the selected dates',
            ], 422);
        }

        $leave = Leave::create([
            'tenant_id' => $user->tenant_id,
            'user_id' => $user->id,
            'leave_type_id' => $validated['leave_type_id'],
            'start_date' => $startDate,
            'end_date' => $endDate,
            'number_of_days' => $numberOfDays,
            'reason' => $validated['reason'] ?? null,
            'is_half_day' => $validated['is_half_day'] ?? false,
            'half_day_type' => $validated['half_day_type'] ?? null,
            'status' => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Leave application submitted successfully',
            'data' => $leave->load('leaveType'),
        ], 201);
    }

    /**
     * Get user's leaves
     */
    public function getMyLeaves(Request $request)
    {
        $user = Auth::user();
        $status = $request->query('status');

        $query = Leave::where('user_id', $user->id)
            ->with('leaveType', 'approvedBy');

        if ($status) {
            $query->where('status', $status);
        }

        $leaves = $query->orderBy('start_date', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $leaves,
        ]);
    }

    /**
     * Get leave details
     */
    public function getLeaveDetails($id)
    {
        $leave = Leave::with('leaveType', 'user', 'approvedBy')
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $leave,
        ]);
    }

    /**
     * Cancel leave application
     */
    public function cancelLeave($id)
    {
        $leave = Leave::findOrFail($id);
        $user = Auth::user();

        if ($leave->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        if ($leave->status !== 'pending' && $leave->status !== 'approved') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot cancel leave with status: ' . $leave->status,
            ], 422);
        }

        $leave->cancel();

        return response()->json([
            'success' => true,
            'message' => 'Leave cancelled successfully',
            'data' => $leave,
        ]);
    }
}
