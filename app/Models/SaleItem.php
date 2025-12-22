<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Services\LoggingService;

class SaleItem extends Model
{
    protected $fillable = [
        'sale_id',
        'product_id',
        'batch_id',
        'quantity',
        'unit_price',
        'total_price',
        'discount_amount',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'discount_amount' => 'decimal:2',
    ];

    /**
     * Get the sale that owns this item
     */
    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    /**
     * Get the product for this sale item
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the batch for this sale item (if exists)
     */
    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    /**
     * Calculate total price based on quantity and unit price
     */
    public function calculateTotalPrice(): void
    {
        $this->total_price = ($this->quantity * $this->unit_price) - $this->discount_amount;
    }

    /**
     * Boot method to automatically calculate total price and update inventory
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($saleItem) {
            $saleItem->calculateTotalPrice();
        });

        static::saved(function ($saleItem) {
            // Update the sale total price when item is saved
            $saleItem->sale->updateTotalPrice();
        });

        static::created(function ($saleItem) {
            // Update inventory when sale item is created
            $saleItem->updateInventory();
        });

        static::deleted(function ($saleItem) {
            // Update the sale total price when item is deleted
            $saleItem->sale->updateTotalPrice();

            // Restore inventory when sale item is deleted
            $saleItem->restoreInventory();
        });
    }

    /**
     * Update inventory levels when item is sold.
     *
     * Primary logic: deduct from sellable (On Shelf) WarehouseStock and
     * keep Batch.quantity in sync. Falls back to legacy batch-only behavior
     * when no tenant/branch or warehouse context is available.
     */
    public function updateInventory(): void
    {
        $product = $this->product;

        if (! $product) {
            return;
        }

        // If no specific batch assigned, use legacy behavior (FIFO across batches).
        if (! $this->batch_id) {
            $this->updateInventoryLegacy();
            return;
        }

        $user = auth()->user();
        $tenantId = $user?->tenant_id
            ?? $product->tenant_id
            ?? (app()->bound('current_tenant') ? app('current_tenant')?->id : null);
        $branchId = $user?->branch_id ?? null;

        if (! $tenantId) {
            // No multi-tenant context detected; fall back to legacy behavior.
            $this->updateInventoryLegacy();
            return;
        }

        // Ensure standard warehouses exist, including On Shelf for this tenant/branch.
        Warehouse::ensureDefaultSystemWarehouses($tenantId, $branchId);

        $sellableWarehouseIds = Warehouse::sellable()
            ->forTenantAndBranch($tenantId, $branchId)
            ->pluck('id');

        if ($sellableWarehouseIds->isEmpty()) {
            throw new \Exception('No sellable warehouses configured for this tenant/branch.');
        }

        $batch = $this->batch;
        if (! $batch) {
            throw new \Exception("Batch not found for sale item ID {$this->id}");
        }

        $requiredQty = (int) $this->quantity;

        // Compute total on-shelf quantity for this product+batch combination.
        $totalOnShelf = WarehouseStock::where('tenant_id', $tenantId)
            ->whereIn('warehouse_id', $sellableWarehouseIds)
            ->where('product_id', $product->id)
            ->where('batch_id', $batch->id)
            ->sum('quantity');

        if ($totalOnShelf < $requiredQty) {
            throw new \Exception("Insufficient on-shelf stock for product: {$product->name}");
        }

        // Deduct from warehouse stocks in a deterministic order inside the transaction.
        $stocks = WarehouseStock::where('tenant_id', $tenantId)
            ->whereIn('warehouse_id', $sellableWarehouseIds)
            ->where('product_id', $product->id)
            ->where('batch_id', $batch->id)
            ->orderBy('warehouse_id')
            ->lockForUpdate()
            ->get();

        $remaining = $requiredQty;

        foreach ($stocks as $stock) {
            if ($remaining <= 0) {
                break;
            }

            $deduct = min($stock->quantity, $remaining);
            if ($deduct <= 0) {
                continue;
            }

            $stock->quantity -= $deduct;
            $stock->save();

            $remaining -= $deduct;
        }

        // Keep global batch quantity in sync.
        $batch->decrement('quantity', $requiredQty);

        // Log inventory change for auditing.
        LoggingService::logInventoryChange(
            'sale',
            $product->id,
            $batch->id,
            -$requiredQty,
            [
                'sale_id' => $this->sale_id,
                'sale_item_id' => $this->id,
                'tenant_id' => $tenantId,
                'branch_id' => $branchId,
            ]
        );
    }

    /**
     * Legacy batch-only inventory update logic used when no warehouse context exists.
     */
    protected function updateInventoryLegacy(): void
    {
        $product = $this->product;

        if (! $product) {
            return;
        }

        if ($this->batch_id) {
            $batch = $this->batch;
            if ($batch && $batch->quantity >= $this->quantity) {
                $batch->quantity -= $this->quantity;
                $batch->save();
            }
        } else {
            // Deduct from oldest batches first (FIFO)
            $remainingQuantity = $this->quantity;
            $batches = $product->batches()
                ->where('quantity', '>', 0)
                ->orderBy('expiry_date')
                ->get();

            foreach ($batches as $batch) {
                if ($remainingQuantity <= 0) {
                    break;
                }

                $deductQuantity = min($batch->quantity, $remainingQuantity);
                $batch->quantity -= $deductQuantity;
                $batch->save();

                $remainingQuantity -= $deductQuantity;
            }
        }
    }

    /**
     * Restore inventory levels when sale item is deleted/cancelled.
     */
    public function restoreInventory(): void
    {
        $product = $this->product;

        if (! $product || ! $this->batch_id) {
            // For now we only support precise restoration when a specific batch is known.
            $this->restoreInventoryLegacy();
            return;
        }

        $batch = $this->batch;
        if (! $batch) {
            return;
        }

        $user = auth()->user();
        $tenantId = $user?->tenant_id
            ?? $product->tenant_id
            ?? (app()->bound('current_tenant') ? app('current_tenant')?->id : null);
        $branchId = $user?->branch_id ?? null;

        if (! $tenantId) {
            $this->restoreInventoryLegacy();
            return;
        }

        // Ensure standard warehouses exist.
        Warehouse::ensureDefaultSystemWarehouses($tenantId, $branchId);

        $onShelfWarehouse = Warehouse::sellable()
            ->forTenantAndBranch($tenantId, $branchId)
            ->first();

        if (! $onShelfWarehouse) {
            $this->restoreInventoryLegacy();
            return;
        }

        $restoreQty = (int) $this->quantity;

        $warehouseStock = WarehouseStock::firstOrNew([
            'tenant_id'    => $tenantId,
            'warehouse_id' => $onShelfWarehouse->id,
            'product_id'   => $product->id,
            'batch_id'     => $batch->id,
        ]);

        $warehouseStock->quantity = ($warehouseStock->quantity ?? 0) + $restoreQty;
        $warehouseStock->save();

        $batch->increment('quantity', $restoreQty);

        LoggingService::logInventoryChange(
            'sale_cancel',
            $product->id,
            $batch->id,
            $restoreQty,
            [
                'sale_id' => $this->sale_id,
                'sale_item_id' => $this->id,
                'tenant_id' => $tenantId,
                'branch_id' => $branchId,
            ]
        );
    }

    /**
     * Legacy restoration logic: just put quantity back to the batch.
     */
    protected function restoreInventoryLegacy(): void
    {
        if ($this->batch_id) {
            $batch = $this->batch;
            if ($batch) {
                $batch->quantity += $this->quantity;
                $batch->save();
            }
        }
        // For FIFO sales without specific batch, we intentionally skip
        // restoration to avoid incorrect distribution across batches.
    }

    /**
     * Get net price after discount
     */
    public function getNetPriceAttribute(): float
    {
        return $this->total_price;
    }
}
