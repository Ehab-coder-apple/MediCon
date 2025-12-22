<?php

namespace App\Http\Controllers;

use App\Models\InventoryReturn;
use App\Models\InventoryReturnItem;
use App\Models\Warehouse;
use App\Models\Supplier;
use App\Models\WarehouseStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryReturnController extends Controller
{
    /**
     * Display a listing of inventory returns
     */
    public function index()
    {
        $routePrefix = $this->getRoutePrefix();
        
        $inventoryReturns = InventoryReturn::where('tenant_id', auth()->user()->tenant_id)
            ->with(['warehouse', 'supplier', 'user'])
            ->orderBy('return_date', 'desc')
            ->paginate(15);

        return view('inventory-returns.index', compact('inventoryReturns', 'routePrefix'));
    }

    /**
     * Show the form for creating a new inventory return
     */
    public function create()
    {
        $routePrefix = $this->getRoutePrefix();
        $tenantId = auth()->user()->tenant_id;

        $warehouses = Warehouse::where('tenant_id', $tenantId)
            ->get();

        $suppliers = Supplier::where('is_active', true)->get();

        return view('inventory-returns.create', compact('warehouses', 'suppliers', 'routePrefix'));
    }

    /**
     * Store a newly created inventory return
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'return_date' => 'required|date|before_or_equal:today',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.batch_id' => 'required|exists:batches,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_cost' => 'required|numeric|min:0',
            'items.*.reason' => 'required|in:slow_moving,nearly_expired,damaged,overstocked,quality_issue,wrong_item,other',
            'items.*.notes' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            DB::beginTransaction();

            // Determine tenant_id: use user's tenant_id, fallback to current_tenant from container
            $tenantId = auth()->user()->tenant_id;
            if (!$tenantId && app()->bound('current_tenant')) {
                $currentTenant = app('current_tenant');
                $tenantId = $currentTenant?->id;
            }

            if (!$tenantId) {
                throw new \Exception('Unable to determine tenant context for inventory return');
            }

            $inventoryReturn = InventoryReturn::create([
                'tenant_id' => $tenantId,
                'warehouse_id' => $validated['warehouse_id'],
                'supplier_id' => $validated['supplier_id'],
                'user_id' => auth()->id(),
                'reference_number' => InventoryReturn::generateReferenceNumber(),
                'return_date' => $validated['return_date'],
                'status' => 'pending',
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($validated['items'] as $itemData) {
                $batch = \App\Models\Batch::findOrFail($itemData['batch_id']);

                InventoryReturnItem::create([
                    'inventory_return_id' => $inventoryReturn->id,
                    'product_id' => $itemData['product_id'],
                    'batch_id' => $itemData['batch_id'],
                    'batch_number' => $batch->batch_number,
                    'quantity' => $itemData['quantity'],
                    'unit_cost' => $itemData['unit_cost'],
                    'total_cost' => $itemData['quantity'] * $itemData['unit_cost'],
                    'reason' => $itemData['reason'],
                    'notes' => $itemData['notes'] ?? null,
                ]);
            }

            $inventoryReturn->updateTotals();

            DB::commit();

            return redirect()->route($this->getRoutePrefix() . 'inventory-returns.show', $inventoryReturn)
                ->with('success', 'Inventory return created successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to create inventory return: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified inventory return
     */
    public function show(InventoryReturn $inventoryReturn)
    {
        $routePrefix = $this->getRoutePrefix();
        $inventoryReturn->load(['warehouse', 'supplier', 'user', 'items.product', 'items.batch']);

        return view('inventory-returns.show', compact('inventoryReturn', 'routePrefix'));
    }

    /**
     * Delete the specified inventory return
     */
    public function destroy(InventoryReturn $inventoryReturn)
    {
        try {
            DB::beginTransaction();

            // Only allow deletion of pending inventory returns
            if ($inventoryReturn->status !== 'pending') {
                return back()->withErrors(['error' => 'Only pending inventory returns can be deleted.']);
            }

            // Delete all associated items
            InventoryReturnItem::where('inventory_return_id', $inventoryReturn->id)->delete();

            // Delete the inventory return
            $inventoryReturn->delete();

            DB::commit();

            return redirect()->route($this->getRoutePrefix() . 'inventory-returns.index')
                ->with('success', 'Inventory return deleted successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to delete inventory return: ' . $e->getMessage()]);
        }
    }

    /**
     * Get the route prefix based on user role
     */
    private function getRoutePrefix(): string
    {
        if (auth()->user()->hasRole('admin')) {
            return 'admin.';
        } elseif (auth()->user()->hasRole('pharmacist')) {
            return 'pharmacist.';
        }
        return '';
    }
}

