<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Batch;
use App\Models\Supplier;
use App\Models\StockReceiving;
use App\Models\StockReceivingItem;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
class StockReceivingController extends Controller
{

    /**
     * Display a listing of stock receiving records
     */
    public function index(): View
    {
        $this->authorize('viewAny', Product::class);

        $receivings = StockReceiving::with(['supplier', 'user', 'items.product'])
            ->orderBy('received_date', 'desc')
            ->paginate(15);

        return view('stock-receiving.index', compact('receivings'));
    }

    /**
     * Show the form for creating a new stock receiving
     */
    public function create(): View
    {
        $this->authorize('create', Product::class);

        $products = Product::where('is_active', true)
            ->orderBy('name')
            ->get();

        $suppliers = Supplier::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('stock-receiving.create', compact('products', 'suppliers'));
    }

    /**
     * Store a newly created stock receiving
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Product::class);

        $validated = $request->validate([
            'supplier_id' => 'nullable|exists:suppliers,id',
            'reference_number' => 'nullable|string|max:255',
            'received_date' => 'required|date',
            'notes' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.batch_number' => 'required|string|max:255',
            'items.*.expiry_date' => 'required|date|after:today',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.bonus_quantity' => 'nullable|integer|min:0',
            'items.*.bonus_notes' => 'nullable|string|max:500',
            'items.*.cost_price' => 'nullable|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            // Create stock receiving record
            $stockReceiving = StockReceiving::create([
                'supplier_id' => $validated['supplier_id'],
                'reference_number' => $validated['reference_number'],
                'received_date' => $validated['received_date'],
                'notes' => $validated['notes'],
                'user_id' => auth()->id(),
                'status' => 'completed',
            ]);

            // Process each item
            foreach ($validated['items'] as $itemData) {
                $this->processStockReceivingItem($stockReceiving, $itemData);
            }

            DB::commit();

            return redirect()->route('admin.stock-receiving.index')
                ->with('success', 'Stock received successfully! Inventory has been updated.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Stock receiving failed', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'data' => $validated
            ]);

            return redirect()->back()
                ->with('error', 'Failed to receive stock: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified stock receiving
     */
    public function show(StockReceiving $stockReceiving): View
    {
        $this->authorize('viewAny', Product::class);

        $stockReceiving->load(['supplier', 'user', 'items.product', 'items.batch']);

        return view('stock-receiving.show', compact('stockReceiving'));
    }

    /**
     * Show quick add stock form for a specific product
     */
    public function quickAdd(Product $product): View
    {
        $this->authorize('update', $product);

        $suppliers = Supplier::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('stock-receiving.quick-add', compact('product', 'suppliers'));
    }

    /**
     * Process quick add stock for a specific product
     */
    public function processQuickAdd(Request $request, Product $product): RedirectResponse
    {
        $this->authorize('update', $product);

        $validated = $request->validate([
            'supplier_id' => 'nullable|exists:suppliers,id',
            'batch_number' => 'required|string|max:255',
            'expiry_date' => 'required|date|after:today',
            'quantity' => 'required|integer|min:1',
            'bonus_quantity' => 'nullable|integer|min:0',
            'bonus_notes' => 'nullable|string|max:500',
            'cost_price' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            DB::beginTransaction();

            // Create stock receiving record
            $stockReceiving = StockReceiving::create([
                'supplier_id' => $validated['supplier_id'],
                'reference_number' => 'QA-' . time(),
                'received_date' => now(),
                'notes' => $validated['notes'] ?? 'Quick add stock for ' . $product->name,
                'user_id' => auth()->id(),
                'status' => 'completed',
            ]);

            // Process the item
            $this->processStockReceivingItem($stockReceiving, [
                'product_id' => $product->id,
                'batch_number' => $validated['batch_number'],
                'expiry_date' => $validated['expiry_date'],
                'quantity' => $validated['quantity'],
                'bonus_quantity' => $validated['bonus_quantity'] ?? 0,
                'bonus_notes' => $validated['bonus_notes'],
                'cost_price' => $validated['cost_price'] ?? $product->cost_price,
            ]);

            DB::commit();

            $totalAdded = $validated['quantity'] + ($validated['bonus_quantity'] ?? 0);
            $bonusText = ($validated['bonus_quantity'] ?? 0) > 0
                ? " (including {$validated['bonus_quantity']} bonus units)"
                : "";

            return redirect()->route('products.show', $product)
                ->with('success', "Successfully added {$totalAdded} units to {$product->name} inventory{$bonusText}!");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Quick add stock failed', [
                'error' => $e->getMessage(),
                'product_id' => $product->id,
                'user_id' => auth()->id(),
                'data' => $validated
            ]);

            return redirect()->back()
                ->with('error', 'Failed to add stock: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Process individual stock receiving item
     */
    private function processStockReceivingItem(StockReceiving $stockReceiving, array $itemData): void
    {
        $product = Product::findOrFail($itemData['product_id']);

        // Check if batch already exists
        $existingBatch = Batch::where('product_id', $product->id)
            ->where('batch_number', $itemData['batch_number'])
            ->first();

        $totalQuantity = $itemData['quantity'] + ($itemData['bonus_quantity'] ?? 0);

        if ($existingBatch) {
            // Update existing batch quantity (includes both paid and bonus)
            $existingBatch->quantity += $totalQuantity;

            // Update cost price if provided and different
            if (isset($itemData['cost_price']) && $itemData['cost_price'] > 0) {
                $existingBatch->cost_price = $itemData['cost_price'];
            }

            $existingBatch->save();
            $batch = $existingBatch;
        } else {
            // Create new batch (includes both paid and bonus quantities)
            $batch = Batch::create([
                'product_id' => $product->id,
                'batch_number' => $itemData['batch_number'],
                'expiry_date' => $itemData['expiry_date'],
                'quantity' => $totalQuantity,
                'cost_price' => $itemData['cost_price'] ?? $product->cost_price,
            ]);
        }

        // Ensure received goods warehouse exists for this tenant/branch
        $user = auth()->user();
        $tenantId = $user?->tenant_id ?? $product->tenant_id ?? app('current_tenant')?->id;
        $branchId = $user?->branch_id;

        if ($tenantId) {
            $receivedWarehouse = Warehouse::getOrCreateSystemWarehouse(
                $tenantId,
                $branchId,
                Warehouse::TYPE_RECEIVED
            );

            // Create or update warehouse stock for this batch in the received warehouse
            $warehouseStock = WarehouseStock::firstOrNew([
                'tenant_id' => $tenantId,
                'warehouse_id' => $receivedWarehouse->id,
                'product_id' => $product->id,
                'batch_id' => $batch->id,
            ]);

            $warehouseStock->quantity = ($warehouseStock->quantity ?? 0) + $totalQuantity;
            $warehouseStock->save();
        }

        // Create stock receiving item record
        StockReceivingItem::create([
            'stock_receiving_id' => $stockReceiving->id,
            'product_id' => $product->id,
            'batch_id' => $batch->id,
            'batch_number' => $itemData['batch_number'],
            'expiry_date' => $itemData['expiry_date'],
            'quantity' => $itemData['quantity'],
            'bonus_quantity' => $itemData['bonus_quantity'] ?? 0,
            'bonus_notes' => $itemData['bonus_notes'] ?? null,
            'cost_price' => $itemData['cost_price'] ?? $product->cost_price,
        ]);

        // Update product's days on hand calculation
        $product->updateDaysOnHand();
    }

    /**
     * Get product details for AJAX requests
     */
    public function getProductDetails(Product $product)
    {
        $this->authorize('view', $product);

        return response()->json([
            'id' => $product->id,
            'name' => $product->name,
            'code' => $product->code,
            'category' => $product->category,
            'current_stock' => $product->active_quantity,
            'cost_price' => $product->cost_price,
            'selling_price' => $product->selling_price,
            'alert_quantity' => $product->alert_quantity,
            'is_low_stock' => $product->is_low_stock,
        ]);
    }
}
