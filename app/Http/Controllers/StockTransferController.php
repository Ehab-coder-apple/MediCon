<?php

namespace App\Http\Controllers;

use App\Models\StockTransfer;
use App\Models\Warehouse;
use App\Services\StockTransferService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StockTransferController extends Controller
{
    public function __construct(private StockTransferService $stockTransferService)
    {
    }

    /**
     * List stock transfers with basic filters.
     */
    public function index(Request $request): View
    {
        $this->authorize('access-admin-dashboard');

        $tenantId = auth()->user()->tenant_id;

        $query = StockTransfer::with(['fromWarehouse', 'toWarehouse', 'user'])
            ->where('tenant_id', $tenantId);

        if ($request->filled('from_warehouse_id')) {
            $query->where('from_warehouse_id', $request->integer('from_warehouse_id'));
        }

        if ($request->filled('to_warehouse_id')) {
            $query->where('to_warehouse_id', $request->integer('to_warehouse_id'));
        }

        if ($request->filled('reference')) {
            $query->where('reference', 'LIKE', '%' . $request->get('reference') . '%');
        }

        if ($request->filled('date_from')) {
            $query->whereDate('transferred_at', '>=', $request->get('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('transferred_at', '<=', $request->get('date_to'));
        }

        $transfers = $query->orderByDesc('transferred_at')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        $warehouses = Warehouse::where('tenant_id', $tenantId)
            ->orderBy('name')
            ->get();

        return view('admin.stock_transfers.index', compact('transfers', 'warehouses'));
    }

    /**
     * Show form to create a new stock transfer.
     */
    public function create(Request $request): View
    {
        $this->authorize('access-admin-dashboard');

        $tenantId = auth()->user()->tenant_id;
        $warehouses = Warehouse::where('tenant_id', $tenantId)
            ->orderBy('name')
            ->get();

        $fromWarehouseId = $request->get('from_warehouse_id');

        return view('admin.stock_transfers.create', compact('warehouses', 'fromWarehouseId'));
    }

    /**
     * Store a new stock transfer.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('access-admin-dashboard');

        $validated = $request->validate([
            'from_warehouse_id' => 'required|exists:warehouses,id',
            'to_warehouse_id' => 'required|different:from_warehouse_id|exists:warehouses,id',
            'reason' => 'nullable|string|max:500',
            'reference' => 'nullable|string|max:100',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.batch_id' => 'nullable|integer|exists:batches,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        try {
            $transfer = $this->stockTransferService->createTransfer(
                (int) $validated['from_warehouse_id'],
                (int) $validated['to_warehouse_id'],
                $validated['items'],
                $validated['reference'] ?? null,
                $validated['reason'] ?? null,
            );
        } catch (\Throwable $e) {
            return back()
                ->withInput()
                ->withErrors(['transfer' => $e->getMessage()]);
        }

        return redirect()
            ->route('admin.stock-transfers.show', $transfer)
            ->with('success', 'Stock transfer created successfully.');
    }

    /**
     * Show a single stock transfer.
     */
    public function show(StockTransfer $stockTransfer): View
    {
        $this->authorize('access-admin-dashboard');
        $this->checkTenantAccess($stockTransfer);

        $stockTransfer->load(['items.product', 'items.batch', 'fromWarehouse', 'toWarehouse', 'user']);

        return view('admin.stock_transfers.show', compact('stockTransfer'));
    }

    private function checkTenantAccess(StockTransfer $stockTransfer): void
    {
        if ($stockTransfer->tenant_id !== auth()->user()->tenant_id) {
            abort(403, 'Unauthorized access to this stock transfer');
        }
    }
}

