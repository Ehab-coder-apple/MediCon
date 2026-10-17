<?php

use App\Models\Warehouse;
use App\Models\WarehouseStock;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Stock Receiving and Purchase receipts used to credit a hidden "Received
 * Goods" (type=received) staging warehouse that nothing ever moved stock
 * out of. As a result, received inventory was invisible on the HQ
 * Inventory Dashboard / Central Warehouse Stock screen (which only reads
 * the tenant's hq_main warehouse), even though Product Management's
 * legacy Stock column (summed from Batch.quantity, tenant-wide) showed it
 * correctly.
 *
 * This one-time backfill moves any existing "received" warehouse stock
 * into each tenant's Central (hq_main) warehouse, merging quantities into
 * existing rows where one already exists for the same product/batch.
 * Tenants with no root Corporate HQ branch yet (so no central warehouse
 * can be resolved) are left untouched.
 */
return new class extends Migration
{
    public function up(): void
    {
        $receivedWarehouses = Warehouse::where('type', Warehouse::TYPE_RECEIVED)->get();

        foreach ($receivedWarehouses as $receivedWarehouse) {
            $stockRows = WarehouseStock::where('warehouse_id', $receivedWarehouse->id)
                ->where('quantity', '>', 0)
                ->get();

            if ($stockRows->isEmpty()) {
                continue;
            }

            try {
                $centralWarehouse = Warehouse::getOrCreateTenantCentralWarehouse($receivedWarehouse->tenant_id);
            } catch (\RuntimeException $e) {
                // No root Corporate HQ branch for this tenant - leave stock
                // where it is.
                continue;
            }

            DB::transaction(function () use ($stockRows, $centralWarehouse) {
                foreach ($stockRows as $stockRow) {
                    $destination = WarehouseStock::firstOrNew([
                        'tenant_id' => $stockRow->tenant_id,
                        'warehouse_id' => $centralWarehouse->id,
                        'product_id' => $stockRow->product_id,
                        'batch_id' => $stockRow->batch_id,
                    ]);

                    $destination->quantity = ($destination->quantity ?? 0) + $stockRow->quantity;
                    $destination->save();

                    $stockRow->quantity = 0;
                    $stockRow->save();
                }
            });
        }
    }

    public function down(): void
    {
        // Not reversible: original per-row received-vs-central split is not
        // preserved once quantities are merged.
    }
};
