<?php

use App\Models\Batch;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Products imported via CSV/Excel without the optional warehouse_quantity /
 * on_shelf_quantity columns got a Batch.quantity recorded but no
 * WarehouseStock row at all - the stock was never placed in any warehouse.
 * Product Management's legacy Stock column (summed from Batch.quantity)
 * showed these units fine, but Central Warehouse Stock, branch
 * requisitions, and every other warehouse-aware screen saw nothing.
 *
 * This backfills the shortfall - for every batch whose quantity exceeds
 * what's already tracked across all WarehouseStock rows for that batch -
 * into the tenant's Central (hq_main) warehouse. Tenants with no root
 * Corporate HQ branch yet (so no central warehouse can be resolved) are
 * left untouched.
 */
return new class extends Migration
{
    public function up(): void
    {
        $batches = Batch::where('quantity', '>', 0)->with('product')->get();

        $trackedByBatch = WarehouseStock::select('batch_id', DB::raw('SUM(quantity) as total'))
            ->groupBy('batch_id')
            ->pluck('total', 'batch_id');

        $centralWarehouseCache = [];

        foreach ($batches as $batch) {
            $tracked = (int) ($trackedByBatch[$batch->id] ?? 0);
            $shortfall = $batch->quantity - $tracked;

            if ($shortfall <= 0) {
                continue;
            }

            $tenantId = $batch->product?->tenant_id;

            if (! $tenantId) {
                continue;
            }

            if (! array_key_exists($tenantId, $centralWarehouseCache)) {
                try {
                    $centralWarehouseCache[$tenantId] = Warehouse::getOrCreateTenantCentralWarehouse($tenantId);
                } catch (\RuntimeException $e) {
                    $centralWarehouseCache[$tenantId] = null;
                }
            }

            $central = $centralWarehouseCache[$tenantId];

            if (! $central) {
                continue;
            }

            $stock = WarehouseStock::firstOrNew([
                'tenant_id' => $tenantId,
                'warehouse_id' => $central->id,
                'product_id' => $batch->product_id,
                'batch_id' => $batch->id,
            ]);

            $stock->quantity = ($stock->quantity ?? 0) + $shortfall;
            $stock->save();
        }
    }

    public function down(): void
    {
        // Not reversible: original (lack of) warehouse placement is not preserved.
    }
};
