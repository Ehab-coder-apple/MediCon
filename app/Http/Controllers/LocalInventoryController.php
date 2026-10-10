<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use App\Services\BranchContextService;
use App\Services\StockTransferService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LocalInventoryController extends Controller
{
    public function __construct(private readonly StockTransferService $stockTransferService)
    {
    }

    /**
     * Move stock internally, within the operator's own active branch, from
     * the Local Backroom Storage warehouse (type = main) onto the active
     * POS counter Dispensing Shelf (type = on_shelf). Reuses the existing
     * StockTransferService, which safely decrements/increments
     * WarehouseStock inside a single locked DB transaction - this endpoint
     * is just a branch-scoped convenience wrapper around it that resolves
     * the two warehouse ids from the operator's active branch context
     * instead of requiring them to be passed in directly.
     */
    public function replenish(Request $request): RedirectResponse|JsonResponse
    {
        $this->authorize('manage-inventory');

        $user = $request->user();
        $branch = BranchContextService::getActiveUserBranchContext($user);

        if (! $branch || ! $branch->isRetail()) {
            return $this->fail($request, 'Backroom-to-shelf replenishment is only available at a retail pharmacy branch.');
        }

        $validated = $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'batch_id' => 'nullable|integer|exists:batches,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $backroom = Warehouse::getOrCreateSystemWarehouse($user->tenant_id, $branch->id, Warehouse::TYPE_MAIN);
        $shelf = Warehouse::getOrCreateSystemWarehouse($user->tenant_id, $branch->id, Warehouse::TYPE_ON_SHELF);

        try {
            $transfer = $this->stockTransferService->createTransfer(
                $backroom->id,
                $shelf->id,
                [[
                    'product_id' => $validated['product_id'],
                    'batch_id' => $validated['batch_id'] ?? null,
                    'quantity' => $validated['quantity'],
                ]],
                null,
                'Backroom to shelf replenishment'
            );
        } catch (\Exception $e) {
            return $this->fail($request, $e->getMessage());
        }

        if ($request->wantsJson()) {
            return response()->json($transfer, 201);
        }

        return back()->with('success', 'Stock moved to the dispensing shelf.');
    }

    private function fail(Request $request, string $message): RedirectResponse|JsonResponse
    {
        if ($request->wantsJson()) {
            return response()->json(['message' => $message], 422);
        }

        return back()->withErrors(['error' => $message])->withInput();
    }
}
