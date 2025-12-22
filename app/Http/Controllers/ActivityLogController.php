<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Carbon\Carbon;

class ActivityLogController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display activity logs
     */
    public function index(Request $request): View
    {
        $user = auth()->user();
        $tenantId = $user->tenant_id;

        // If user has no tenant_id, try to get the first active tenant
        if (!$tenantId) {
            $tenant = Tenant::where('is_active', true)->first();
            if (!$tenant) {
                abort(403, 'Access denied: No active tenant found');
            }
            $tenantId = $tenant->id;
        }

        $query = ActivityLog::where('tenant_id', $tenantId)
            ->with(['user'])
            ->orderBy('created_at', 'desc');

        // Filter by category
        if ($request->filled('category')) {
            $query->byCategory($request->category);
        }

        // Filter by action
        if ($request->filled('action')) {
            $query->byAction($request->action);
        }

        // Filter by entity type
        if ($request->filled('entity_type')) {
            $query->byEntityType($request->entity_type);
        }

        // Filter by severity
        if ($request->filled('severity')) {
            $query->bySeverity($request->severity);
        }

        // Filter by date range
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $startDate = Carbon::parse($request->start_date)->startOfDay();
            $endDate = Carbon::parse($request->end_date)->endOfDay();
            $query->dateRange($startDate, $endDate);
        }

        // Search by description or entity name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('entity_name', 'like', "%{$search}%");
            });
        }

        $logs = $query->paginate(50);

        // Get unique categories and actions for filters from database
        $dbCategories = ActivityLog::where('tenant_id', $tenantId)
            ->distinct()
            ->pluck('category')
            ->sort()
            ->toArray();

        $dbActions = ActivityLog::where('tenant_id', $tenantId)
            ->distinct()
            ->pluck('action')
            ->sort()
            ->toArray();

        $dbEntityTypes = ActivityLog::where('tenant_id', $tenantId)
            ->distinct()
            ->pluck('entity_type')
            ->sort()
            ->toArray();

        // Predefined options
        $predefinedCategories = ['sales', 'purchases', 'inventory', 'users', 'products', 'customers', 'suppliers', 'system'];
        $predefinedActions = ['created', 'updated', 'deleted', 'viewed', 'completed', 'cancelled'];
        $predefinedEntityTypes = ['Sale', 'Purchase', 'Product', 'Customer', 'User', 'Supplier', 'Inventory'];

        // Merge database values with predefined options (database values take precedence)
        $categories = collect(array_unique(array_merge($dbCategories, $predefinedCategories)))->sort()->values();
        $actions = collect(array_unique(array_merge($dbActions, $predefinedActions)))->sort()->values();
        $entityTypes = collect(array_unique(array_merge($dbEntityTypes, $predefinedEntityTypes)))->sort()->values();

        return view('activity-logs.index', compact('logs', 'categories', 'actions', 'entityTypes'));
    }

    /**
     * Show activity log details
     */
    public function show(ActivityLog $activityLog): View
    {
        $this->authorize('view', $activityLog);
        return view('activity-logs.show', compact('activityLog'));
    }

    /**
     * Export activity logs
     */
    public function export(Request $request)
    {
        $user = auth()->user();
        $tenantId = $user->tenant_id;

        // If user has no tenant_id, try to get the first active tenant
        if (!$tenantId) {
            $tenant = Tenant::where('is_active', true)->first();
            if (!$tenant) {
                abort(403, 'Access denied: No active tenant found');
            }
            $tenantId = $tenant->id;
        }

        $query = ActivityLog::where('tenant_id', $tenantId)
            ->with(['user'])
            ->orderBy('created_at', 'desc');

        // Apply same filters as index
        if ($request->filled('category')) {
            $query->byCategory($request->category);
        }
        if ($request->filled('action')) {
            $query->byAction($request->action);
        }
        if ($request->filled('entity_type')) {
            $query->byEntityType($request->entity_type);
        }
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $startDate = Carbon::parse($request->start_date)->startOfDay();
            $endDate = Carbon::parse($request->end_date)->endOfDay();
            $query->dateRange($startDate, $endDate);
        }

        $logs = $query->get();

        // Generate CSV
        $filename = 'activity-logs-' . now()->format('Y-m-d-His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($logs) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Date', 'User', 'Action', 'Entity Type', 'Entity Name', 'Description', 'Category', 'Severity']);

            foreach ($logs as $log) {
                fputcsv($file, [
                    $log->created_at->format('Y-m-d H:i:s'),
                    $log->user?->name ?? 'System',
                    $log->getActionLabel(),
                    $log->entity_type,
                    $log->entity_name,
                    $log->description,
                    $log->getCategoryLabel(),
                    $log->severity,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Clear old activity logs
     */
    public function clearOld(Request $request): RedirectResponse
    {
        $days = $request->input('days', 90);
        $cutoffDate = now()->subDays($days);

        ActivityLog::where('tenant_id', auth()->user()->tenant_id)
            ->where('created_at', '<', $cutoffDate)
            ->delete();

        return redirect()->back()->with('success', "Activity logs older than {$days} days have been deleted.");
    }
}
