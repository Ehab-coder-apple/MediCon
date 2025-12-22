<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Supplier;
use App\Models\Tenant;
use App\Services\ActivityLogService;
use App\Services\AutoPurchaseOrderService;
use App\Traits\HasRoleBasedRouting;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\Response;

class PurchaseController extends Controller
{
    use HasRoleBasedRouting;

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $purchases = Purchase::with(['supplier', 'user'])
            ->withCount('purchaseItems')
            ->latest('purchase_date')
            ->paginate(15);

        return view('purchases.index', compact('purchases'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $suppliers = Supplier::active()->orderBy('name')->get();
        $products = Product::where('is_active', true)->orderBy('name')->get();
        $referenceNumber = Purchase::generateReferenceNumber();

        return view('purchases.create', compact('suppliers', 'products', 'referenceNumber'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'purchase_date' => 'required|date',
            'reference_number' => 'required|string',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_cost' => 'required|numeric|min:0',
            'items.*.batch_number' => 'nullable|string',
            'items.*.expiry_date' => 'nullable|date|after:today',
        ]);

        // Get tenant_id from multiple sources (before transaction)
        $tenantId = auth()->user()->tenant_id;
        if (!$tenantId && app()->bound('current_tenant')) {
            $currentTenant = app('current_tenant');
            $tenantId = $currentTenant?->id;
        }

        // If still no tenant_id, get the first active tenant (fallback for super admins)
        if (!$tenantId) {
            $tenant = Tenant::where('is_active', true)->first();
            if ($tenant) {
                $tenantId = $tenant->id;
            }
        }

        $purchase = null;
        DB::transaction(function () use ($validated, $request, &$purchase, $tenantId) {
            // Ensure reference number is unique (regenerate if needed)
            $referenceNumber = $validated['reference_number'];
            if (Purchase::where('reference_number', $referenceNumber)->exists()) {
                $referenceNumber = Purchase::generateReferenceNumber();
            }

            // Create the purchase
            $purchase = Purchase::create([
                'supplier_id' => $validated['supplier_id'],
                'user_id' => auth()->id(),
                'tenant_id' => $tenantId,
                'purchase_date' => $validated['purchase_date'],
                'reference_number' => $referenceNumber,
                'notes' => $validated['notes'],
                'total_cost' => 0, // Will be calculated from items
                'status' => 'pending',
                'payment_status' => 'unpaid',
                'payment_method' => 'credit',
                'paid_amount' => 0,
                'balance_due' => 0, // Will be set to total_cost after items are added
                'due_date' => now()->addDays(30), // Default 30 days payment terms
            ]);

            // Create purchase items
            foreach ($validated['items'] as $itemData) {
                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $itemData['product_id'],
                    'quantity' => $itemData['quantity'],
                    'unit_cost' => $itemData['unit_cost'],
                ]);
            }

            // Update total cost (this is handled automatically by the PurchaseItem model)
        });

        // Log to activity log
        if ($purchase) {
            $purchase->load('supplier');
            // Pass tenant_id explicitly to avoid null issues
            ActivityLogService::log(
                'created',
                'Purchase',
                $purchase->id,
                $purchase->reference_number,
                "Purchase #{$purchase->reference_number} created from {$purchase->supplier->name}",
                null,
                'purchases',
                'info',
                $tenantId
            );
        }

        return $this->redirectToIndex('purchases', 'Purchase order created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Purchase $purchase): View
    {
        $purchase->load(['supplier', 'user', 'purchaseItems.product']);

        return view('purchases.show', compact('purchase'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Purchase $purchase): View
    {
        $suppliers = Supplier::active()->orderBy('name')->get();
        $products = Product::where('is_active', true)->orderBy('name')->get();
        $purchase->load('purchaseItems.product');

        return view('purchases.edit', compact('purchase', 'suppliers', 'products'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Purchase $purchase): RedirectResponse
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'purchase_date' => 'required|date',
            'notes' => 'nullable|string',
            'status' => 'required|in:pending,completed,cancelled',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_cost' => 'required|numeric|min:0',
            'items.*.batch_number' => 'nullable|string',
            'items.*.expiry_date' => 'nullable|date|after:today',
            'items.*.id' => 'nullable|exists:purchase_items,id',
            'deleted_items' => 'nullable|array',
            'deleted_items.*' => 'exists:purchase_items,id',
        ]);

        DB::transaction(function () use ($validated, $purchase) {
            // Update purchase basic information
            $purchase->update([
                'supplier_id' => $validated['supplier_id'],
                'purchase_date' => $validated['purchase_date'],
                'notes' => $validated['notes'],
                'status' => $validated['status'],
            ]);

            // Handle deleted items
            if (!empty($validated['deleted_items'])) {
                PurchaseItem::whereIn('id', $validated['deleted_items'])
                    ->where('purchase_id', $purchase->id)
                    ->delete();
            }

            // Update or create purchase items
            foreach ($validated['items'] as $itemData) {
                if (!empty($itemData['id'])) {
                    // Update existing item
                    $purchaseItem = PurchaseItem::where('id', $itemData['id'])
                        ->where('purchase_id', $purchase->id)
                        ->first();

                    if ($purchaseItem) {
                        $purchaseItem->update([
                            'product_id' => $itemData['product_id'],
                            'quantity' => $itemData['quantity'],
                            'unit_cost' => $itemData['unit_cost'],
                        ]);
                    }
                } else {
                    // Create new item
                    PurchaseItem::create([
                        'purchase_id' => $purchase->id,
                        'product_id' => $itemData['product_id'],
                        'quantity' => $itemData['quantity'],
                        'unit_cost' => $itemData['unit_cost'],
                    ]);
                }
            }

            // Recalculate total cost
            $purchase->updateTotalCost();
        });

        // Log to activity log
        $purchase->load('supplier');
        ActivityLogService::logPurchase('updated', $purchase);

        return $this->redirectToIndex('purchases', 'Purchase updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Purchase $purchase): RedirectResponse
    {
        // Only allow deletion of pending purchases
        if ($purchase->status !== 'pending') {
            return $this->redirectToIndex('purchases', 'Only pending purchases can be deleted.', 'error');
        }

        // Log to activity log before deletion
        $purchase->load('supplier');
        ActivityLogService::logPurchase('deleted', $purchase);

        $purchase->delete();

        return $this->redirectToIndex('purchases', 'Purchase deleted successfully.');
    }

    /**
     * Mark purchase as completed
     */
    public function complete(Purchase $purchase): RedirectResponse
    {
        $purchase->update(['status' => 'completed']);

        // Log to activity log
        $purchase->load('supplier');
        ActivityLogService::logPurchase('completed', $purchase);

        return redirect()->route('purchases.show', $purchase)
            ->with('success', 'Purchase marked as completed.');
    }

    /**
     * Get product details for AJAX requests
     */
    public function getProductDetails(Product $product)
    {
        return response()->json([
            'id' => $product->id,
            'name' => $product->name,
            'code' => $product->code,
            'cost_price' => $product->cost_price,
            'selling_price' => $product->selling_price,
        ]);
    }

    /**
     * Export purchase order as PDF
     */
    public function exportPdf(Purchase $purchase): Response
    {
        // Load relationships
        $purchase->load(['supplier', 'user', 'tenant', 'purchaseItems.product']);

        // Generate PDF
        $pdf = Pdf::loadView('purchases.pdf', compact('purchase'))
            ->setPaper('a4')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', true);

        // Return PDF download
        return $pdf->download('purchase-order-' . $purchase->reference_number . '.pdf');
    }

    /**
     * Show the auto-generation form with inventory analysis
     */
    public function showAutoGenerate(): View
    {
        $service = new AutoPurchaseOrderService();
        $itemsToReorder = $service->analyzeInventory();
        $analysis = $service->groupItemsBySupplier($itemsToReorder);
        $suppliers = Supplier::active()->orderBy('name')->get();

        return view('purchases.auto-generate', compact('analysis', 'suppliers', 'itemsToReorder'));
    }

    /**
     * Generate purchase orders based on inventory analysis
     */
    public function generateAutoPurchaseOrders(Request $request): RedirectResponse
    {
        // Log the request data for debugging
        \Log::info('=== AUTO-GENERATE REQUEST RECEIVED ===');
        \Log::info('Request method: ' . $request->method());
        \Log::info('Request path: ' . $request->path());
        \Log::info('All request data:', $request->all());
        \Log::info('Auto-generate request', [
            'supplier_id' => $request->input('supplier_id'),
            'items' => $request->input('items'),
        ]);

        \Log::info('DEBUG: After auto-generate request log');
        \Log::info('About to enter try-catch block');

        try {
            \Log::info('About to validate', [
                'supplier_id' => $request->input('supplier_id'),
                'items' => $request->input('items'),
                'items_type' => gettype($request->input('items')),
            ]);

            $validated = $request->validate([
                'supplier_id' => 'required|exists:suppliers,id',
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.quantity' => 'required|integer|min:1',
            ]);

            \Log::info('Validation passed', ['validated_data' => $validated]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation failed', ['errors' => $e->errors(), 'messages' => $e->messages()]);
            throw $e;
        }

        try {
            \Log::info('DEBUG: Entering second try block for purchase creation');
            // Get tenant_id from multiple sources (before transaction)
            $tenantId = auth()->user()->tenant_id;
            if (!$tenantId && app()->bound('current_tenant')) {
                $currentTenant = app('current_tenant');
                $tenantId = $currentTenant?->id;
            }

            // If still no tenant_id, get the first active tenant (fallback for super admins)
            if (!$tenantId) {
                $tenant = Tenant::where('is_active', true)->first();
                if ($tenant) {
                    $tenantId = $tenant->id;
                }
            }

            \Log::info('DEBUG: Tenant ID resolved', ['tenant_id' => $tenantId]);

            DB::beginTransaction();
            \Log::info('DEBUG: Transaction started');

            $supplier = Supplier::findOrFail($validated['supplier_id']);
            \Log::info('DEBUG: Supplier found', ['supplier_id' => $supplier->id]);

            $referenceNumber = Purchase::generateReferenceNumber();
            \Log::info('DEBUG: Reference number generated', ['reference_number' => $referenceNumber]);

            $totalCost = 0;

            // Create purchase order
            \Log::info('DEBUG: About to create purchase', ['supplier_id' => $supplier->id, 'tenant_id' => $tenantId]);

            try {
                $purchase = Purchase::create([
                    'supplier_id' => $supplier->id,
                    'user_id' => auth()->id(),
                    'tenant_id' => $tenantId,
                    'purchase_date' => now()->toDateString(),
                    'reference_number' => $referenceNumber,
                    'status' => 'draft',
                    'payment_status' => 'unpaid',
                    'notes' => 'Auto-generated purchase order',
                    'total_cost' => 0,
                    'paid_amount' => 0,
                    'balance_due' => 0,
                ]);
                \Log::info('Purchase created successfully', ['purchase_id' => $purchase->id, 'reference_number' => $purchase->reference_number]);
            } catch (\Exception $createEx) {
                \Log::error('Failed to create purchase', [
                    'message' => $createEx->getMessage(),
                    'file' => $createEx->getFile(),
                    'line' => $createEx->getLine()
                ]);
                throw $createEx;
            }

            // Add items to purchase order
            foreach ($validated['items'] as $item) {
                $product = Product::findOrFail($item['product_id']);
                $itemTotalCost = $item['quantity'] * $product->cost_price;
                $totalCost += $itemTotalCost;

                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'unit_cost' => $product->cost_price,
                    'total_cost' => $itemTotalCost,
                ]);
            }

            // Update purchase total cost
            $purchase->update([
                'total_cost' => $totalCost,
                'balance_due' => $totalCost,
            ]);

            DB::commit();

            \Log::info('Transaction committed', ['purchase_id' => $purchase->id]);
            \Log::info('Redirecting to', ['route' => $this->getRoutePrefix() . 'purchases.show', 'purchase_id' => $purchase->id]);

            return redirect()->route($this->getRoutePrefix() . 'purchases.show', $purchase)
                ->with('success', "Purchase order #{$purchase->reference_number} created successfully. Please review and confirm.");
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Exception in auto-generate', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()
                ->with('error', 'Failed to generate purchase order: ' . $e->getMessage());
        }
    }

    /**
     * Show the stock receiving form for a completed purchase order
     */
    public function showReceiveStock(Purchase $purchase): View
    {
        // Only allow receiving stock for completed purchases
        if ($purchase->status !== 'completed') {
            abort(403, 'Only completed purchase orders can receive stock.');
        }

        $purchase->load('purchaseItems.product');

        return view('purchases.receive-stock', compact('purchase'));
    }

    /**
     * Process stock receiving for a purchase order
     */
    public function processReceiveStock(Request $request, Purchase $purchase): RedirectResponse
    {
        // Only allow receiving stock for completed purchases
        if ($purchase->status !== 'completed') {
            abort(403, 'Only completed purchase orders can receive stock.');
        }

        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.purchase_item_id' => 'required|exists:purchase_items,id',
            'items.*.received_quantity' => 'required|integer|min:0',
            'items.*.batch_number' => 'required|string|max:255',
            'items.*.expiry_date' => 'required|date|after:today',
        ]);

        try {
            DB::beginTransaction();

            // Get or create StockReceiving record
            $stockReceiving = \App\Models\StockReceiving::create([
                'supplier_id' => $purchase->supplier_id,
                'reference_number' => 'PO-' . $purchase->reference_number . '-' . now()->format('YmdHis'),
                'received_date' => now()->toDateString(),
                'notes' => 'Stock received from purchase order: ' . $purchase->reference_number,
                'user_id' => auth()->id(),
                'status' => 'completed',
            ]);

            // Process each item
            foreach ($validated['items'] as $itemData) {
                $purchaseItem = PurchaseItem::findOrFail($itemData['purchase_item_id']);

                // Only process items with received quantity > 0
                if ($itemData['received_quantity'] > 0) {
                    $this->createStockReceivingItem($stockReceiving, $purchaseItem, $itemData);
                }
            }

            // Update stock receiving totals
            $stockReceiving->updateTotals();

            DB::commit();

            return redirect()->route($this->getRoutePrefix() . 'purchases.show', $purchase)
                ->with('success', 'Stock received successfully! Inventory has been updated.');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Stock receiving from purchase order failed', [
                'purchase_id' => $purchase->id,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return back()->withErrors(['error' => 'Failed to process stock receiving: ' . $e->getMessage()]);
        }
    }

    /**
     * Create a stock receiving item and update inventory
     */
    private function createStockReceivingItem($stockReceiving, $purchaseItem, $itemData): void
    {
        $product = $purchaseItem->product;
        $tenantId = auth()->user()?->tenant_id ?? $product->tenant_id;

        // Check if batch already exists
        $batch = \App\Models\Batch::where('product_id', $product->id)
            ->where('batch_number', $itemData['batch_number'])
            ->first();

        if ($batch) {
            // Update existing batch quantity
            $batch->quantity += $itemData['received_quantity'];
            $batch->save();
        } else {
            // Create new batch
            $batch = \App\Models\Batch::create([
                'product_id' => $product->id,
                'batch_number' => $itemData['batch_number'],
                'expiry_date' => $itemData['expiry_date'],
                'quantity' => $itemData['received_quantity'],
                'cost_price' => $purchaseItem->unit_cost,
            ]);
        }

        // Ensure received goods warehouse exists
        $branchId = auth()->user()?->branch_id;
        if ($tenantId) {
            $receivedWarehouse = \App\Models\Warehouse::getOrCreateSystemWarehouse(
                $tenantId,
                $branchId,
                \App\Models\Warehouse::TYPE_RECEIVED
            );

            // Create or update warehouse stock
            $warehouseStock = \App\Models\WarehouseStock::firstOrNew([
                'tenant_id' => $tenantId,
                'warehouse_id' => $receivedWarehouse->id,
                'product_id' => $product->id,
                'batch_id' => $batch->id,
            ]);

            $warehouseStock->quantity = ($warehouseStock->quantity ?? 0) + $itemData['received_quantity'];
            $warehouseStock->save();
        }

        // Create stock receiving item record
        \App\Models\StockReceivingItem::create([
            'stock_receiving_id' => $stockReceiving->id,
            'product_id' => $product->id,
            'batch_id' => $batch->id,
            'batch_number' => $itemData['batch_number'],
            'expiry_date' => $itemData['expiry_date'],
            'quantity' => $itemData['received_quantity'],
            'bonus_quantity' => 0,
            'cost_price' => $purchaseItem->unit_cost,
        ]);

        // Update product's days on hand calculation
        $product->updateDaysOnHand();
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
