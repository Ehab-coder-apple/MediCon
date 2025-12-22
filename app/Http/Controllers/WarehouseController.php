<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Warehouse;
use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WarehouseController extends Controller
{
    /**
     * Display a listing of the resource.
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

        $query = Warehouse::with('branch')
            ->where('tenant_id', $tenantId);

        if ($request->filled('type')) {
            $query->where('type', $request->get('type'));
        }

        if ($request->filled('is_sellable')) {
            $query->where('is_sellable', (bool) $request->get('is_sellable'));
        }

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->get('branch_id'));
        }

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('type', 'LIKE', "%{$search}%");
            });
        }

        $warehouses = $query->orderBy('name')->paginate(15)->withQueryString();
        $branches = Branch::where('tenant_id', $tenantId)
            ->orderBy('name')
            ->get();

        return view('admin.warehouses.index', compact('warehouses', 'branches'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
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

        $branches = Branch::where('tenant_id', $tenantId)
            ->orderBy('name')
            ->get();

        $types = [
            'main' => 'Main Warehouse',
            'on_shelf' => 'On Shelf (Sellable)',
            'received' => 'Received Goods',
            'expired' => 'Expired Goods',
            'damaged' => 'Damaged Goods',
            'returns' => 'Returns Warehouse',
            'custom' => 'Custom',
        ];

        return view('admin.warehouses.create', compact('branches', 'types'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('access-admin-dashboard');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:50',
            'branch_id' => 'nullable|exists:branches,id',
            'is_sellable' => 'boolean',
            'specifications' => 'nullable|string',
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
        $validated['is_sellable'] = $request->boolean('is_sellable');

        Warehouse::create($validated);

        return redirect()->route('admin.warehouses.index')
            ->with('success', 'Warehouse created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Warehouse $warehouse): View
    {
        $this->authorize('access-admin-dashboard');
        $this->checkTenantAccess($warehouse);

        $warehouse->load(['branch', 'stocks.product', 'stocks.batch']);

        return view('admin.warehouses.show', compact('warehouse'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Warehouse $warehouse): View
    {
        $this->authorize('access-admin-dashboard');
        $this->checkTenantAccess($warehouse);

        $branches = Branch::where('tenant_id', auth()->user()->tenant_id)
            ->orderBy('name')
            ->get();

        $types = [
            'main' => 'Main Warehouse',
            'on_shelf' => 'On Shelf (Sellable)',
            'received' => 'Received Goods',
            'expired' => 'Expired Goods',
            'damaged' => 'Damaged Goods',
            'returns' => 'Returns Warehouse',
            'custom' => 'Custom',
        ];

        return view('admin.warehouses.edit', compact('warehouse', 'branches', 'types'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Warehouse $warehouse): RedirectResponse
    {
        $this->authorize('access-admin-dashboard');
        $this->checkTenantAccess($warehouse);

        if ($warehouse->is_system ?? false) {
            $allowedTypes = ['main', 'on_shelf', 'received', 'expired', 'damaged', 'returns'];
        } else {
            $allowedTypes = ['main', 'on_shelf', 'received', 'expired', 'damaged', 'returns', 'custom'];
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:' . implode(',', $allowedTypes),
            'branch_id' => 'nullable|exists:branches,id',
            'is_sellable' => 'boolean',
            'specifications' => 'nullable|string',
        ]);

        $validated['is_sellable'] = $request->boolean('is_sellable');

        $warehouse->update($validated);

        return redirect()->route('admin.warehouses.index')
            ->with('success', 'Warehouse updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Warehouse $warehouse): RedirectResponse
    {
        $this->authorize('access-admin-dashboard');
        $this->checkTenantAccess($warehouse);

        if ($warehouse->is_system ?? false) {
            return redirect()->route('admin.warehouses.index')
                ->with('error', 'System warehouses cannot be deleted.');
        }

        if ($warehouse->stocks()->exists()) {
            return redirect()->route('admin.warehouses.index')
                ->with('error', 'Cannot delete a warehouse that has stock.');
        }

        $warehouse->delete();

        return redirect()->route('admin.warehouses.index')
            ->with('success', 'Warehouse deleted successfully.');
    }

    /**
     * Ensure the warehouse belongs to the current tenant.
     */
    private function checkTenantAccess(Warehouse $warehouse): void
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

        if ($warehouse->tenant_id !== $tenantId) {
            abort(403, 'Unauthorized access to this warehouse');
        }
    }
}
