<?php

namespace App\Services;

use App\Models\Batch;
use App\Models\Product;
use App\Models\StockTransferOrder;
use App\Models\StockTransferOrderItem;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use Exception;
use Illuminate\Support\Facades\DB;

class StockTransferOrderService
{
    /**
     * Initiate a stock transfer order from a Central (HQ Main) warehouse to
     * a branch's Local (backroom/shelf) warehouse. No stock is moved yet;
     * WarehouseStock is only touched when the order is marked 'received'.
     *
     * @param  array<int,array<string,int|float|null>>  $items  Each item: [product_id, batch_id, quantity, currency_unit_cost]
     */
    public function createOrder(int $sourceWarehouseId, int $destinationWarehouseId, array $items): StockTransferOrder
    {
        $user = auth()->user();

        if (! $user) {
            throw new Exception('User must be authenticated to initiate a stock transfer order.');
        }

        if ($sourceWarehouseId === $destinationWarehouseId) {
            throw new Exception('Source and destination warehouses must be different.');
        }

        return DB::transaction(function () use ($sourceWarehouseId, $destinationWarehouseId, $items, $user) {
            $source = Warehouse::with('branch')->findOrFail($sourceWarehouseId);
            $destination = Warehouse::with('branch')->findOrFail($destinationWarehouseId);

            if ($source->tenant_id !== $user->tenant_id || $destination->tenant_id !== $user->tenant_id) {
                throw new Exception('You can only transfer stock between warehouses in your organization.');
            }

            // Enforce the Central -> Local direction: HQ pushes stock down
            // to a retail branch's local warehouse.
            if (! in_array($source->type, Warehouse::CENTRAL_TYPES, true)) {
                throw new Exception('Stock transfer orders must originate from a Central (HQ Main) warehouse.');
            }

            if (! in_array($destination->type, Warehouse::LOCAL_TYPES, true)) {
                throw new Exception('Stock transfer orders must be destined for a Local (branch) warehouse.');
            }

            if (! $destination->branch || ! $destination->branch->isRetail()) {
                throw new Exception('The destination warehouse must belong to a retail pharmacy branch.');
            }

            if (empty($items)) {
                throw new Exception('At least one item is required to initiate a stock transfer order.');
            }

            $order = StockTransferOrder::create([
                'tenant_id' => $user->tenant_id,
                'source_warehouse_id' => $source->id,
                'destination_warehouse_id' => $destination->id,
                'status' => StockTransferOrder::STATUS_PENDING,
                'initiated_by' => $user->id,
            ]);

            foreach ($items as $item) {
                $productId = (int) ($item['product_id'] ?? 0);
                $batchId = isset($item['batch_id']) ? (int) $item['batch_id'] : null;
                $quantity = (int) ($item['quantity'] ?? 0);
                $unitCost = (float) ($item['currency_unit_cost'] ?? 0);

                if ($productId <= 0 || $quantity <= 0) {
                    continue;
                }

                Product::findOrFail($productId);
                if ($batchId) {
                    Batch::where('product_id', $productId)->findOrFail($batchId);
                }

                StockTransferOrderItem::create([
                    'stock_transfer_order_id' => $order->id,
                    'product_id' => $productId,
                    'batch_id' => $batchId,
                    'quantity' => $quantity,
                    'currency_unit_cost' => $unitCost,
                ]);
            }

            LoggingService::logAudit('stock_transfer_order_initiated', StockTransferOrder::class, $order->id, [
                'source_warehouse_id' => $source->id,
                'destination_warehouse_id' => $destination->id,
            ]);

            return $order->load('items', 'sourceWarehouse', 'destinationWarehouse');
        });
    }

    /**
     * Mark a pending order as dispatched (in transit). No stock is moved.
     */
    public function markInTransit(StockTransferOrder $order): StockTransferOrder
    {
        if (! $order->isPending()) {
            throw new Exception('Only a pending order can be marked as in transit.');
        }

        $order->update(['status' => StockTransferOrder::STATUS_IN_TRANSIT]);

        return $order;
    }

    /**
     * Cancel a pending or in-transit order. No stock has moved yet, so this
     * is a simple status change.
     */
    public function cancelOrder(StockTransferOrder $order): StockTransferOrder
    {
        if (! $order->isPending() && ! $order->isInTransit()) {
            throw new Exception('Only a pending or in-transit order can be cancelled.');
        }

        $order->update(['status' => StockTransferOrder::STATUS_CANCELLED]);

        return $order;
    }

    /**
     * Confirm receipt of a stock transfer order at the destination branch.
     * This is the only point at which WarehouseStock is actually moved:
     * the source warehouse stock is safely decremented and the destination
     * warehouse stock is incremented, atomically and with row locking so
     * concurrent transfers/sales cannot oversell the source stock.
     *
     * $receivingUser must be a worker assigned to the order's destination
     * branch; this is enforced here (in addition to the route-level Gate)
     * so the guard holds even if the service is called directly.
     */
    public function receiveOrder(StockTransferOrder $order, User $receivingUser): StockTransferOrder
    {
        if (! $order->isPending() && ! $order->isInTransit()) {
            throw new Exception('This order has already been received or was cancelled.');
        }

        if ($receivingUser->tenant_id !== $order->tenant_id) {
            throw new Exception('You can only receive stock transfer orders within your own organization.');
        }

        return DB::transaction(function () use ($order, $receivingUser) {
            // Lock the order row first to prevent two workers from both
            // "receiving" the same order concurrently.
            /** @var StockTransferOrder $lockedOrder */
            $lockedOrder = StockTransferOrder::lockForUpdate()->findOrFail($order->id);

            if (! $lockedOrder->isPending() && ! $lockedOrder->isInTransit()) {
                throw new Exception('This order has already been received or was cancelled.');
            }

            $destinationWarehouse = Warehouse::lockForUpdate()->findOrFail($lockedOrder->destination_warehouse_id);

            // Re-verify the receiving worker is actually assigned to the
            // destination branch (defense in depth, on top of the Gate).
            $destinationBranchId = $destinationWarehouse->branch_id;
            $belongsToBranch = $destinationBranchId
                && ($receivingUser->branch_id === $destinationBranchId
                    || $receivingUser->branches()->where('branches.id', $destinationBranchId)->exists());

            if (! $belongsToBranch) {
                throw new Exception('You can only receive stock transfer orders addressed to your own branch.');
            }

            $tenantId = $lockedOrder->tenant_id;
            $sourceWarehouseId = $lockedOrder->source_warehouse_id;

            $items = $lockedOrder->items()->get();

            if ($items->isEmpty()) {
                throw new Exception('This order has no items to receive.');
            }

            foreach ($items as $item) {
                $sourceStockQuery = WarehouseStock::where('tenant_id', $tenantId)
                    ->where('warehouse_id', $sourceWarehouseId)
                    ->where('product_id', $item->product_id);

                if ($item->batch_id) {
                    $sourceStockQuery->where('batch_id', $item->batch_id);
                } else {
                    $sourceStockQuery->whereNull('batch_id');
                }

                /** @var WarehouseStock|null $sourceStock */
                $sourceStock = $sourceStockQuery->lockForUpdate()->first();

                if (! $sourceStock || $sourceStock->quantity < $item->quantity) {
                    $productName = $item->product?->name ?? "product #{$item->product_id}";
                    throw new Exception("Insufficient stock in the source (Central) warehouse for {$productName}. The order cannot be received.");
                }

                // Safely decrement the source (Central) warehouse stock.
                $sourceStock->quantity -= $item->quantity;
                $sourceStock->save();

                // Safely increment the destination (Local) warehouse stock.
                $destStock = WarehouseStock::firstOrNew([
                    'tenant_id' => $tenantId,
                    'warehouse_id' => $destinationWarehouse->id,
                    'product_id' => $item->product_id,
                    'batch_id' => $item->batch_id,
                ]);
                $destStock->quantity = ($destStock->quantity ?? 0) + $item->quantity;
                $destStock->save();

                LoggingService::logInventoryChange('stock_transfer_order_received', $item->product_id, $item->batch_id ?? 0, $item->quantity, [
                    'stock_transfer_order_id' => $lockedOrder->id,
                    'source_warehouse_id' => $sourceWarehouseId,
                    'destination_warehouse_id' => $destinationWarehouse->id,
                    'received_by' => $receivingUser->id,
                ]);
            }

            $lockedOrder->update([
                'status' => StockTransferOrder::STATUS_RECEIVED,
                'received_by' => $receivingUser->id,
            ]);

            LoggingService::logAudit('stock_transfer_order_received', StockTransferOrder::class, $lockedOrder->id, [
                'source_warehouse_id' => $sourceWarehouseId,
                'destination_warehouse_id' => $destinationWarehouse->id,
                'received_by' => $receivingUser->id,
            ]);

            return $lockedOrder->fresh(['items', 'sourceWarehouse', 'destinationWarehouse', 'receiver']);
        });
    }
}
