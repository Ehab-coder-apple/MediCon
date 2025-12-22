<?php

namespace App\Services;

use App\Models\Batch;
use App\Models\Product;
use Illuminate\Support\Collection;

class AutoPurchaseOrderService
{
    /**
     * Analyze inventory and identify items that need reordering
     */
    public function analyzeInventory(): Collection
    {
        $itemsToReorder = collect();

        // Get all active products
        $products = Product::where('is_active', true)->get();

        foreach ($products as $product) {
            $activeQuantity = $product->active_quantity;
            $alertQuantity = $product->alert_quantity ?? 0;

            // Check for out-of-stock items
            if ($activeQuantity == 0) {
                $itemsToReorder->push([
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_code' => $product->code,
                    'current_quantity' => 0,
                    'reason' => 'out_of_stock',
                    'suggested_quantity' => $this->getDefaultReorderQuantity($product),
                    'cost_price' => $product->cost_price,
                ]);
            }
            // Check for low-stock items
            elseif ($activeQuantity <= $alertQuantity && $alertQuantity > 0) {
                $quantityNeeded = $alertQuantity - $activeQuantity;
                $itemsToReorder->push([
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_code' => $product->code,
                    'current_quantity' => $activeQuantity,
                    'reason' => 'low_stock',
                    'suggested_quantity' => $quantityNeeded,
                    'cost_price' => $product->cost_price,
                ]);
            }
        }

        // Check for expired/expiring soon products
        $expiringBatches = Batch::where('expiry_date', '<=', now()->addDays(30))
            ->where('expiry_date', '>', now())
            ->where('quantity', '>', 0)
            ->get();

        foreach ($expiringBatches as $batch) {
            $product = $batch->product;
            
            // Check if this product is already in the reorder list
            $existingItem = $itemsToReorder->firstWhere('product_id', $product->id);
            
            if (!$existingItem) {
                $itemsToReorder->push([
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_code' => $product->code,
                    'current_quantity' => $batch->quantity,
                    'reason' => 'expiring_soon',
                    'suggested_quantity' => $batch->quantity,
                    'cost_price' => $product->cost_price,
                    'batch_id' => $batch->id,
                    'expiry_date' => $batch->expiry_date,
                ]);
            }
        }

        return $itemsToReorder;
    }

    /**
     * Get default reorder quantity for a product
     */
    private function getDefaultReorderQuantity(Product $product): int
    {
        $alertQuantity = $product->alert_quantity ?? 10;
        // Default to 2x the alert quantity
        return $alertQuantity * 2;
    }

    /**
     * Group items by supplier (user will select suppliers)
     */
    public function groupItemsBySupplier(Collection $items): array
    {
        return [
            'items' => $items,
            'total_items' => $items->count(),
            'total_cost_estimate' => $items->sum(function ($item) {
                return $item['suggested_quantity'] * $item['cost_price'];
            }),
        ];
    }
}

