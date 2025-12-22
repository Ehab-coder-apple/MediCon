<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    /**
     * Display attendance records
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Attendance::class);

        $query = Attendance::with(['user', 'branch', 'tenant']);

        // Filter by date range
        if ($request->filled('start_date')) {
            $query->whereDate('attendance_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('attendance_date', '<=', $request->end_date);
        }

        // Filter by user
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by branch
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by geofence compliance
        if ($request->filled('geofence_status')) {
            if ($request->geofence_status === 'within') {
                $query->where('check_in_within_geofence', true);
            } elseif ($request->geofence_status === 'outside') {
                $query->where('check_in_within_geofence', false);
            }
        }

        $attendances = $query->orderBy('attendance_date', 'desc')
            ->orderBy('check_in_time', 'desc')
            ->paginate(20)
            ->withQueryString();

        $users = User::where('role_id', '!=', 1)->get(); // Exclude admins
        $branches = Branch::active()->get();

        return view('admin.attendance.index', compact('attendances', 'users', 'branches'));
    }

    /**
     * Show attendance details
     */
    public function show(Attendance $attendance): View
    {
        $this->authorize('view', $attendance);

        $attendance->load(['user', 'branch', 'tenant']);

        return view('admin.attendance.show', compact('attendance'));
    }

    /**
     * Export attendance records
     */
    public function export(Request $request)
    {
        $this->authorize('viewAny', Attendance::class);

        $query = Attendance::with(['user', 'branch']);

        // Apply same filters as index
        if ($request->filled('start_date')) {
            $query->whereDate('attendance_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('attendance_date', '<=', $request->end_date);
        }
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        $attendances = $query->orderBy('attendance_date', 'desc')->get();

        // Generate CSV
        $filename = 'attendance_' . now()->format('Y-m-d_H-i-s') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($attendances) {
            $file = fopen('php://output', 'w');

            // Header row
            fputcsv($file, [
                'Date',
                'Employee',
                'Branch',
                'Check-in Time',
                'Check-out Time',
                'Duration (Hours)',
                'Check-in Location',
                'Check-out Location',
                'Within Geofence',
                'Status',
            ]);

            // Data rows
            foreach ($attendances as $record) {
                fputcsv($file, [
                    $record->attendance_date->format('Y-m-d'),
                    $record->user->name,
                    $record->branch?->name ?? 'N/A',
                    $record->check_in_time?->format('H:i:s') ?? 'N/A',
                    $record->check_out_time?->format('H:i:s') ?? 'N/A',
                    $record->total_minutes_worked ? round($record->total_minutes_worked / 60, 2) : 'N/A',
                    $record->check_in_latitude . ', ' . $record->check_in_longitude,
                    $record->check_out_latitude ? $record->check_out_latitude . ', ' . $record->check_out_longitude : 'N/A',
                    $record->check_in_within_geofence ? 'Yes' : 'No',
                    ucfirst($record->status),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get attendance statistics
     */
    public function statistics(Request $request): View
    {
        $this->authorize('viewAny', Attendance::class);

        $startDate = $request->filled('start_date')
            ? Carbon::parse($request->start_date)
            : now()->startOfMonth();

        $endDate = $request->filled('end_date')
            ? Carbon::parse($request->end_date)
            : now()->endOfMonth();

        $query = Attendance::whereBetween('attendance_date', [$startDate, $endDate]);

        // Apply branch filter if provided
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        $totalRecords = $query->count();
        $daysPresent = $query->where('status', 'checked_out')->count();
        $incompleteDays = $query->where('status', 'checked_in')->count();
        $geofenceViolations = $query->where('check_in_within_geofence', false)->orWhere('check_out_within_geofence', false)->count();

        $averageMinutes = $query->where('status', 'checked_out')
            ->avg('total_minutes_worked') ?? 0;
        $averageHours = round($averageMinutes / 60, 1);

        // Status breakdown
        $statusPending = Attendance::whereBetween('attendance_date', [$startDate, $endDate])
            ->where('status', 'pending')
            ->count();
        $statusCheckedIn = Attendance::whereBetween('attendance_date', [$startDate, $endDate])
            ->where('status', 'checked_in')
            ->count();
        $statusCheckedOut = Attendance::whereBetween('attendance_date', [$startDate, $endDate])
            ->where('status', 'checked_out')
            ->count();
        $statusIncomplete = Attendance::whereBetween('attendance_date', [$startDate, $endDate])
            ->where('status', 'incomplete')
            ->count();

        // Geofence breakdown
        $geofenceWithin = Attendance::whereBetween('attendance_date', [$startDate, $endDate])
            ->where('check_in_within_geofence', true)
            ->where('check_out_within_geofence', true)
            ->count();
        $geofenceOutside = Attendance::whereBetween('attendance_date', [$startDate, $endDate])
            ->where(function ($q) {
                $q->where('check_in_within_geofence', false)
                  ->orWhere('check_out_within_geofence', false);
            })
            ->count();

        $stats = [
            'total_records' => $totalRecords,
            'days_present' => $daysPresent,
            'incomplete_days' => $incompleteDays,
            'geofence_violations' => $geofenceViolations,
            'average_hours' => $averageHours,
            'status_pending' => $statusPending,
            'status_checked_in' => $statusCheckedIn,
            'status_checked_out' => $statusCheckedOut,
            'status_incomplete' => $statusIncomplete,
            'geofence_within' => $geofenceWithin,
            'geofence_outside' => $geofenceOutside,
        ];

        $branches = Branch::active()->get();

        return view('admin.attendance.statistics', compact('stats', 'branches'));
    }
}

