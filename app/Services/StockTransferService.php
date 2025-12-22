<?php

namespace App\Services;

use App\Models\Batch;
use App\Models\Product;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use Exception;
use Illuminate\Support\Facades\DB;

class StockTransferService
{
    /**
     * Create a stock transfer between two warehouses and update WarehouseStock.
     *
     * @param  int         $fromWarehouseId
     * @param  int         $toWarehouseId
     * @param  array<int,array<string,int|null>>  $items  Each item: [product_id, batch_id, quantity]
     * @param  string|null $reference
     * @param  string|null $reason
     */
    public function createTransfer(
        int $fromWarehouseId,
        int $toWarehouseId,
        array $items,
        ?string $reference = null,
        ?string $reason = null
    ): StockTransfer {
        $user = auth()->user();

        if (! $user) {
            throw new Exception('User must be authenticated to create a stock transfer.');
        }

        if ($fromWarehouseId === $toWarehouseId) {
            throw new Exception('Source and destination warehouses must be different.');
        }

        return DB::transaction(function () use ($fromWarehouseId, $toWarehouseId, $items, $reference, $reason, $user) {
            $fromWarehouse = Warehouse::lockForUpdate()->findOrFail($fromWarehouseId);
            $toWarehouse = Warehouse::lockForUpdate()->findOrFail($toWarehouseId);

            if ($fromWarehouse->tenant_id !== $user->tenant_id || $toWarehouse->tenant_id !== $user->tenant_id) {
                throw new Exception('You can only transfer stock between warehouses in your tenant.');
            }

            $tenantId = $user->tenant_id;

            $transfer = StockTransfer::create([
                'tenant_id' => $tenantId,
                'from_warehouse_id' => $fromWarehouse->id,
                'to_warehouse_id' => $toWarehouse->id,
                'user_id' => $user->id,
                'reference' => $reference ?: $this->generateReference(),
                'reason' => $reason,
                'status' => 'completed',
                'transferred_at' => now(),
            ]);

            foreach ($items as $item) {
                $productId = (int) ($item['product_id'] ?? 0);
                $batchId = isset($item['batch_id']) ? (int) $item['batch_id'] : null;
                $quantity = (int) ($item['quantity'] ?? 0);

                if ($productId <= 0 || $quantity <= 0) {
                    continue;
                }

                $product = Product::findOrFail($productId);
                $batch = null;

                if ($batchId) {
                    $batch = Batch::where('product_id', $productId)->findOrFail($batchId);
                }

                $sourceStockQuery = WarehouseStock::where('tenant_id', $tenantId)
                    ->where('warehouse_id', $fromWarehouse->id)
                    ->where('product_id', $productId);

                if ($batchId) {
                    $sourceStockQuery->where('batch_id', $batchId);
                }

                /** @var WarehouseStock|null $sourceStock */
                $sourceStock = $sourceStockQuery->lockForUpdate()->first();

                if (! $sourceStock || $sourceStock->quantity < $quantity) {
                    $productName = $product->name;
                    throw new Exception("Insufficient stock in source warehouse for product: {$productName}");
                }

                $sourceStock->quantity -= $quantity;
                $sourceStock->save();

                $destStockAttrs = [
                    'tenant_id' => $tenantId,
                    'warehouse_id' => $toWarehouse->id,
                    'product_id' => $productId,
                    'batch_id' => $batchId,
                ];

                $destStock = WarehouseStock::firstOrNew($destStockAttrs);
                $destStock->quantity = ($destStock->quantity ?? 0) + $quantity;
                $destStock->save();

                StockTransferItem::create([
                    'stock_transfer_id' => $transfer->id,
                    'product_id' => $productId,
                    'batch_id' => $batchId,
                    'quantity' => $quantity,
                ]);

                // Log inventory movement between warehouses for auditing
                LoggingService::logInventoryChange('transfer', $productId, $batchId ?? 0, $quantity, [
                    'stock_transfer_id' => $transfer->id,
                    'from_warehouse_id' => $fromWarehouse->id,
                    'to_warehouse_id' => $toWarehouse->id,
                    'user_id' => $user->id,
                ]);
            }

            LoggingService::logAudit(
                'stock_transfer_created',
                StockTransfer::class,
                $transfer->id,
                [
                    'from_warehouse_id' => $fromWarehouse->id,
                    'to_warehouse_id' => $toWarehouse->id,
                    'reference' => $transfer->reference,
                ]
            );

            return $transfer->load('items.product', 'items.batch', 'fromWarehouse', 'toWarehouse', 'user');
        });
    }

    protected function generateReference(): string
    {
        return 'TRF-' . now()->format('Ymd-His') . '-' . substr((string) microtime(true), -4);
    }
}

