<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;
use App\Models\Batch;
use App\Models\WarehouseStock;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use DB;

class PurchaseReturnController extends Controller
{
    /**
     * Display a listing of purchase returns
     */
    public function index(): View
    {
        // Determine tenant_id: use user's tenant_id, fallback to current_tenant from container
        $tenantId = auth()->user()->tenant_id;
        if (!$tenantId && app()->bound('current_tenant')) {
            $currentTenant = app('current_tenant');
            $tenantId = $currentTenant?->id;
        }

        $returns = PurchaseReturn::with(['purchase', 'supplier', 'user'])
            ->where('tenant_id', $tenantId)
            ->orderBy('return_date', 'desc')
            ->paginate(15);

        return view('purchases.returns.index', compact('returns'));
    }

    /**
     * Show the form for creating a new purchase return
     */
    public function create(Purchase $purchase): View
    {
        // Verify purchase is completed
        if ($purchase->status !== 'completed') {
            abort(403, 'Only completed purchase orders can be returned.');
        }

        // Get purchase items with batch information
        $purchaseItems = $purchase->purchaseItems()->with(['product', 'batch'])->get();

        return view('purchases.returns.create', compact('purchase', 'purchaseItems'));
    }

    /**
     * Store a newly created purchase return in storage
     */
    public function store(Request $request, Purchase $purchase): RedirectResponse
    {
        // Verify purchase is completed
        if ($purchase->status !== 'completed') {
            return back()->withErrors(['error' => 'Only completed purchase orders can be returned.']);
        }

        $validated = $request->validate([
            'return_date' => 'required|date|before_or_equal:today',
            'items' => 'required|array|min:1',
            'items.*.purchase_item_id' => 'required|exists:purchase_items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.batch_id' => 'required|exists:batches,id',
            'items.*.reason' => 'required|string|in:damaged,expired,wrong_item,quality_issue,other',
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
                throw new \Exception('Unable to determine tenant context for purchase return');
            }

            // Create purchase return
            $purchaseReturn = PurchaseReturn::create([
                'tenant_id' => $tenantId,
                'purchase_id' => $purchase->id,
                'supplier_id' => $purchase->supplier_id,
                'user_id' => auth()->id(),
                'reference_number' => PurchaseReturn::generateReferenceNumber(),
                'return_date' => $validated['return_date'],
                'status' => 'pending',
                'notes' => $validated['notes'] ?? null,
            ]);

            // Process each return item
            foreach ($validated['items'] as $itemData) {
                $purchaseItem = $purchase->purchaseItems()->findOrFail($itemData['purchase_item_id']);
                $batch = Batch::findOrFail($itemData['batch_id']);

                // Validate quantity doesn't exceed received quantity
                if ($itemData['quantity'] > $purchaseItem->quantity) {
                    throw new \Exception("Return quantity cannot exceed received quantity for {$purchaseItem->product->name}");
                }

                // Create return item
                PurchaseReturnItem::create([
                    'purchase_return_id' => $purchaseReturn->id,
                    'product_id' => $purchaseItem->product_id,
                    'batch_id' => $batch->id,
                    'batch_number' => $batch->batch_number,
                    'quantity' => $itemData['quantity'],
                    'unit_cost' => $purchaseItem->unit_cost,
                    'reason' => $itemData['reason'],
                    'notes' => $itemData['notes'] ?? null,
                ]);
            }

            // Update totals
            $purchaseReturn->updateTotals();

            DB::commit();

            return redirect()->route($this->getRoutePrefix() . 'purchase-returns.show', $purchaseReturn)
                ->with('success', 'Return request created successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Purchase return creation failed', [
                'purchase_id' => $purchase->id,
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors(['error' => 'Failed to create return request: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified purchase return
     */
    public function show(PurchaseReturn $purchaseReturn): View
    {
        $purchaseReturn->load(['purchase', 'supplier', 'user', 'items.product', 'items.batch']);
        return view('purchases.returns.show', compact('purchaseReturn'));
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

