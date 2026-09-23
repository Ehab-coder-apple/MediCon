<?php

namespace App\Http\Controllers;

use App\Models\StockTransferOrder;
use App\Services\StockTransferOrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class StockTransferOrderController extends Controller
{
    public function __construct(private readonly StockTransferOrderService $service)
    {
    }

    /**
     * List stock transfer orders for the current tenant. Kept as a plain
     * JSON endpoint for now; the dashboard views for HQ inventory managers
     * and branch workers are wired up in a later phase.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $orders = StockTransferOrder::where('tenant_id', $user->tenant_id)
            ->with(['sourceWarehouse', 'destinationWarehouse', 'initiator', 'receiver', 'items.product'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->get('status')))
            ->orderByDesc('created_at')
            ->paginate(20);

        return response()->json($orders);
    }

    public function show(Request $request, StockTransferOrder $stockTransferOrder): JsonResponse
    {
        $this->authorizeTenant($request, $stockTransferOrder);

        return response()->json(
            $stockTransferOrder->load(['sourceWarehouse', 'destinationWarehouse', 'initiator', 'receiver', 'items.product', 'items.batch'])
        );
    }

    /**
     * Initiate a new stock transfer order from a Central (HQ) warehouse to
     * a branch's Local warehouse. Restricted to users with the
     * 'initiate_stock_transfers' permission (e.g. HQ Inventory Manager).
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $this->authorize('initiate-stock-transfer-orders');

        $validated = $request->validate([
            'source_warehouse_id' => 'required|integer|exists:warehouses,id',
            'destination_warehouse_id' => 'required|integer|exists:warehouses,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.batch_id' => 'nullable|integer|exists:batches,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.currency_unit_cost' => 'nullable|numeric|min:0',
        ]);

        try {
            $order = $this->service->createOrder(
                (int) $validated['source_warehouse_id'],
                (int) $validated['destination_warehouse_id'],
                $validated['items']
            );
        } catch (\Exception $e) {
            return $this->fail($request, $e->getMessage());
        }

        if ($request->wantsJson()) {
            return response()->json($order, 201);
        }

        return back()->with('success', 'Stock transfer order initiated successfully.');
    }

    /**
     * Mark a pending order as dispatched (in transit). No stock movement.
     */
    public function markInTransit(Request $request, StockTransferOrder $stockTransferOrder): RedirectResponse|JsonResponse
    {
        $this->authorize('initiate-stock-transfer-orders');
        $this->authorizeTenant($request, $stockTransferOrder);

        try {
            $order = $this->service->markInTransit($stockTransferOrder);
        } catch (\Exception $e) {
            return $this->fail($request, $e->getMessage());
        }

        if ($request->wantsJson()) {
            return response()->json($order);
        }

        return back()->with('success', 'Stock transfer order marked as in transit.');
    }

    /**
     * Cancel a pending or in-transit order.
     */
    public function cancel(Request $request, StockTransferOrder $stockTransferOrder): RedirectResponse|JsonResponse
    {
        $this->authorize('initiate-stock-transfer-orders');
        $this->authorizeTenant($request, $stockTransferOrder);

        try {
            $order = $this->service->cancelOrder($stockTransferOrder);
        } catch (\Exception $e) {
            return $this->fail($request, $e->getMessage());
        }

        if ($request->wantsJson()) {
            return response()->json($order);
        }

        return back()->with('success', 'Stock transfer order cancelled.');
    }

    /**
     * Confirm receipt of a stock transfer order at the destination branch.
     * Restricted to branch workers (Gate + service-level branch check);
     * safely decrements source warehouse stock and increments destination
     * warehouse stock atomically.
     */
    public function receive(Request $request, StockTransferOrder $stockTransferOrder): RedirectResponse|JsonResponse
    {
        $this->authorize('receive-stock-transfer-orders');
        $this->authorizeTenant($request, $stockTransferOrder);

        try {
            $order = $this->service->receiveOrder($stockTransferOrder, $request->user());
        } catch (\Exception $e) {
            return $this->fail($request, $e->getMessage());
        }

        if ($request->wantsJson()) {
            return response()->json($order);
        }

        return back()->with('success', 'Stock transfer order received successfully.');
    }

    private function authorizeTenant(Request $request, StockTransferOrder $order): void
    {
        if ($order->tenant_id !== $request->user()->tenant_id) {
            abort(403, 'Unauthorized access to this stock transfer order.');
        }
    }

    private function fail(Request $request, string $message): RedirectResponse|JsonResponse
    {
        if ($request->wantsJson()) {
            return response()->json(['message' => $message], 422);
        }

        return back()->withErrors(['error' => $message])->withInput();
    }
}
