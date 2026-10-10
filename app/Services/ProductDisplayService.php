<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductDisplaySetting;
use App\Models\FeaturedProduct;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;

class ProductDisplayService
{
    /**
     * Get products based on tenant's display strategy, strictly isolated to
     * the given branch's On Shelf warehouse stock when a branch is
     * supplied. Legacy/imported products that have never been placed into
     * any warehouse (zero WarehouseStock rows at all) still fall back to
     * global batch-level stock, mirroring the same fallback
     * Product::getOnShelfQuantityAttribute() already uses, so older
     * tenants aren't broken by the branch-aware POS catalog.
     */
    public function getDisplayProducts(int $tenantId, ?int $branchId = null): Collection
    {
        $setting = ProductDisplaySetting::forTenant($tenantId);
        $limit = $setting->products_limit;

        $onShelfWarehouseIds = $this->resolveOnShelfWarehouseIds($tenantId, $branchId);

        $products = match ($setting->display_strategy) {
            'fast_moving' => $this->getFastMovingProducts($tenantId, $limit, $onShelfWarehouseIds),
            'high_stock' => $this->getHighStockProducts($tenantId, $limit, $onShelfWarehouseIds),
            'nearly_expired' => $this->getNearlyExpiredProducts($tenantId, $limit, $onShelfWarehouseIds),
            'custom_selection' => $this->getCustomSelectionProducts($tenantId, $limit, $onShelfWarehouseIds),
            default => $this->getFastMovingProducts($tenantId, $limit, $onShelfWarehouseIds),
        };

        $this->annotateBranchStockFields($products, $onShelfWarehouseIds);

        return $products;
    }

    /**
     * On Shelf warehouse ids strictly scoped to the given branch (or
     * tenant-wide/legacy warehouses with no branch_id when $branchId is
     * null). Ensures the branch's system warehouses exist first so a
     * freshly-created branch isn't treated as having zero On Shelf stock.
     */
    private function resolveOnShelfWarehouseIds(int $tenantId, ?int $branchId): SupportCollection
    {
        if ($branchId) {
            Warehouse::ensureDefaultSystemWarehouses($tenantId, $branchId);
        }

        return Warehouse::where('tenant_id', $tenantId)
            ->where('type', Warehouse::TYPE_ON_SHELF)
            ->when(
                $branchId,
                fn (Builder $q) => $q->where('branch_id', $branchId),
                fn (Builder $q) => $q->whereNull('branch_id')
            )
            ->pluck('id');
    }

    /**
     * Constrain a product query to only products with sellable stock in the
     * given On Shelf warehouse(s), OR legacy products that have never been
     * placed into any warehouse at all (tracked at the batch level only) -
     * which must still independently satisfy $legacyBatchConstraint (e.g.
     * quantity > 0 and not expired) to be considered in stock. This is the
     * single data-isolation gate the POS catalog goes through: a product
     * with on-shelf stock at a different branch is correctly excluded,
     * since it has warehouseStocks rows but none in $warehouseIds.
     */
    private function withBranchStockConstraint(Builder $query, SupportCollection $warehouseIds, \Closure $legacyBatchConstraint): Builder
    {
        return $query->where(function (Builder $q) use ($warehouseIds, $legacyBatchConstraint) {
            $q->whereHas('warehouseStocks', function (Builder $wq) use ($warehouseIds) {
                $wq->whereIn('warehouse_id', $warehouseIds)->where('quantity', '>', 0);
            })->orWhere(function (Builder $legacyQ) use ($legacyBatchConstraint) {
                $legacyQ->whereDoesntHave('warehouseStocks')
                    ->whereHas('batches', $legacyBatchConstraint);
            });
        });
    }

    /**
     * Eager-load the On Shelf warehouse stock (with batch) used to annotate
     * each product with its branch-specific batch number(s) and nearest
     * expiry date for the POS card display.
     */
    private function withBranchStockEagerLoad(Builder $query, SupportCollection $warehouseIds): Builder
    {
        return $query->with(['warehouseStocks' => function ($q) use ($warehouseIds) {
            $q->whereIn('warehouse_id', $warehouseIds)
                ->where('quantity', '>', 0)
                ->with('batch');
        }]);
    }

    /**
     * Annotate each product with branch-scoped display fields consumed by
     * the POS card (invoices/create.blade.php):
     *   - branch_stock_quantity: on-shelf quantity at this branch (or the
     *     legacy global active_quantity fallback)
     *   - branch_batch_number: the nearest-expiry batch number at this
     *     branch, for display
     *   - branch_nearest_expiry: the nearest expiry date among this
     *     branch's on-shelf batches, used to color-code the card
     *     (amber/red when expiring within 90 days)
     */
    private function annotateBranchStockFields(Collection $products, SupportCollection $warehouseIds): void
    {
        foreach ($products as $product) {
            $stocks = $product->relationLoaded('warehouseStocks') ? $product->warehouseStocks : collect();

            if ($stocks->isNotEmpty()) {
                $product->branch_stock_quantity = (int) $stocks->sum('quantity');

                $nearestStock = $stocks
                    ->filter(fn ($stock) => $stock->batch)
                    ->sortBy(fn ($stock) => $stock->batch->expiry_date)
                    ->first();

                $product->branch_batch_number = $nearestStock?->batch?->batch_number;
                $product->branch_nearest_expiry = $nearestStock?->batch?->expiry_date;
            } else {
                // Legacy fallback: no warehouse records at all for this
                // product, so report its global non-expired batch stock.
                $nearestBatch = $product->batches
                    ->filter(fn ($batch) => $batch->quantity > 0 && $batch->expiry_date > now())
                    ->sortBy('expiry_date')
                    ->first();

                $product->branch_stock_quantity = (int) $product->active_quantity;
                $product->branch_batch_number = $nearestBatch?->batch_number;
                $product->branch_nearest_expiry = $nearestBatch?->expiry_date;
            }
        }
    }

    /**
     * Get fast moving products (highest sales volume in last 30 days)
     */
    private function getFastMovingProducts(int $tenantId, int $limit, SupportCollection $onShelfWarehouseIds): Collection
    {
        $query = Product::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->with(['batches' => function($query) {
                $query->where('quantity', '>', 0)
                      ->where('expiry_date', '>', now())
                      ->orderBy('expiry_date');
            }]);

        $query = $this->withBranchStockEagerLoad($query, $onShelfWarehouseIds);
        $query = $this->withBranchStockConstraint($query, $onShelfWarehouseIds, function (Builder $bq) {
            $bq->where('quantity', '>', 0)->where('expiry_date', '>', now());
        });

        return $query->withCount(['saleItems as sales_count' => function($query) {
                $query->whereHas('sale', function($q) {
                    $q->where('sale_date', '>=', now()->subDays(30))
                      ->where('status', 'completed');
                });
            }])
            ->orderByDesc('sales_count')
            ->limit($limit)
            ->get();
    }

    /**
     * Get high stock products (highest available quantity)
     */
    private function getHighStockProducts(int $tenantId, int $limit, SupportCollection $onShelfWarehouseIds): Collection
    {
        $query = Product::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->with(['batches' => function($query) {
                $query->where('quantity', '>', 0)
                      ->where('expiry_date', '>', now())
                      ->orderBy('expiry_date');
            }])
            ->withSum('batches', 'quantity');

        $query = $this->withBranchStockEagerLoad($query, $onShelfWarehouseIds);
        $query = $this->withBranchStockConstraint($query, $onShelfWarehouseIds, function (Builder $bq) {
            $bq->where('quantity', '>', 0)->where('expiry_date', '>', now());
        });

        return $query->orderByDesc('batches_sum_quantity')
            ->limit($limit)
            ->get();
    }

    /**
     * Get nearly expired products (closest expiry dates first)
     */
    private function getNearlyExpiredProducts(int $tenantId, int $limit, SupportCollection $onShelfWarehouseIds): Collection
    {
        $query = Product::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->with(['batches' => function($query) {
                $query->where('quantity', '>', 0)
                      ->where('expiry_date', '>', now())
                      ->where('expiry_date', '<=', now()->addDays(90))
                      ->orderBy('expiry_date');
            }]);

        $query = $this->withBranchStockEagerLoad($query, $onShelfWarehouseIds);
        $query = $this->withBranchStockConstraint($query, $onShelfWarehouseIds, function (Builder $bq) {
            $bq->where('quantity', '>', 0)
                ->where('expiry_date', '>', now())
                ->where('expiry_date', '<=', now()->addDays(90));
        });

        $products = $query->get();

        $sorted = $products->sortBy(function($product) {
            return $product->batches->min('expiry_date');
        })->slice(0, $limit)->values();

        return $sorted;
    }

    /**
     * Get custom selected products in display order
     */
    private function getCustomSelectionProducts(int $tenantId, int $limit, SupportCollection $onShelfWarehouseIds): Collection
    {
        $productIds = FeaturedProduct::forTenant($tenantId)
            ->limit($limit)
            ->pluck('product_id')
            ->toArray();

        if (empty($productIds)) {
            return collect();
        }

        $query = Product::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->with(['batches' => function($query) {
                $query->where('quantity', '>', 0)
                      ->where('expiry_date', '>', now())
                      ->orderBy('expiry_date');
            }])
            ->whereIn('id', $productIds);

        $query = $this->withBranchStockEagerLoad($query, $onShelfWarehouseIds);
        $query = $this->withBranchStockConstraint($query, $onShelfWarehouseIds, function (Builder $bq) {
            $bq->where('quantity', '>', 0)->where('expiry_date', '>', now());
        });

        $products = $query->get();

        // Sort by the order in featured_products
        $sorted = $products->sortBy(function($product) use ($productIds) {
            return array_search($product->id, $productIds);
        })->values();

        return $sorted;
    }
}
