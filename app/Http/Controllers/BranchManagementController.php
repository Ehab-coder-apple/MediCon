<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class BranchManagementController extends Controller
{
    /**
     * Display all branches
     */
    public function index(Request $request): View
    {
        $this->authorize('access-admin-dashboard');

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

        $query = Branch::where('tenant_id', $tenantId);

        // Filter by status
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        // Filter by geofencing requirement
        if ($request->filled('geofencing')) {
            if ($request->geofencing === 'required') {
                $query->where('requires_geofencing', true);
            } elseif ($request->geofencing === 'not_required') {
                $query->where('requires_geofencing', false);
            }
        }

        $branches = $query->orderBy('name')->paginate(20)->withQueryString();

        return view('admin.branches.index', compact('branches'));
    }

    /**
     * Show create branch form
     */
    public function create(): View
    {
        $this->authorize('access-admin-dashboard');
        return view('admin.branches.create');
    }

    /**
     * Store new branch
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('access-admin-dashboard');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:branches,code',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'country' => 'required|string|max:100',
            'postal_code' => 'required|string|max:20',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'geofence_radius' => 'required|integer|min:50|max:5000',
            'requires_geofencing' => 'boolean',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'manager_name' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

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

        $validated['tenant_id'] = $tenantId;
        $validated['created_by'] = auth()->id();
        $validated['updated_by'] = auth()->id();

        Branch::create($validated);

        return redirect()->route('admin.branches.index')
            ->with('success', 'Branch created successfully');
    }

    /**
     * Show branch details
     */
    public function show(Branch $branch): View
    {
        $this->authorize('access-admin-dashboard');
        $this->checkTenantAccess($branch);

        return view('admin.branches.show', compact('branch'));
    }

    /**
     * Show edit branch form
     */
    public function edit(Branch $branch): View
    {
        $this->authorize('access-admin-dashboard');
        $this->checkTenantAccess($branch);

        return view('admin.branches.edit', compact('branch'));
    }

    /**
     * Update branch
     */
    public function update(Request $request, Branch $branch): RedirectResponse
    {
        $this->authorize('access-admin-dashboard');
        $this->checkTenantAccess($branch);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'country' => 'required|string|max:100',
            'postal_code' => 'required|string|max:20',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'geofence_radius' => 'required|integer|min:50|max:5000',
            'requires_geofencing' => 'boolean',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'manager_name' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $validated['updated_by'] = auth()->id();

        $branch->update($validated);

        return redirect()->route('admin.branches.show', $branch)
            ->with('success', 'Branch updated successfully');
    }

    /**
     * Check if branch belongs to user's tenant
     */
    private function checkTenantAccess(Branch $branch): void
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

        if ($branch->tenant_id !== $tenantId) {
            abort(403, 'Unauthorized access to this branch');
        }
    }
}

