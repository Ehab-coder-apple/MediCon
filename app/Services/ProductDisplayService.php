<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductDisplaySetting;
use App\Models\FeaturedProduct;
use Illuminate\Database\Eloquent\Collection;

class ProductDisplayService
{
    /**
     * Get products based on tenant's display strategy
     */
    public function getDisplayProducts(int $tenantId): Collection
    {
        $setting = ProductDisplaySetting::forTenant($tenantId);
        $limit = $setting->products_limit;

        return match ($setting->display_strategy) {
            'fast_moving' => $this->getFastMovingProducts($tenantId, $limit),
            'high_stock' => $this->getHighStockProducts($tenantId, $limit),
            'nearly_expired' => $this->getNearlyExpiredProducts($tenantId, $limit),
            'custom_selection' => $this->getCustomSelectionProducts($tenantId, $limit),
            default => $this->getFastMovingProducts($tenantId, $limit),
        };
    }

    /**
     * Get fast moving products (highest sales volume in last 30 days)
     */
    private function getFastMovingProducts(int $tenantId, int $limit): Collection
    {
        return Product::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->with(['batches' => function($query) {
                $query->where('quantity', '>', 0)
                      ->where('expiry_date', '>', now())
                      ->orderBy('expiry_date');
            }])
            ->whereHas('batches', function($query) {
                $query->where('quantity', '>', 0)
                      ->where('expiry_date', '>', now());
            })
            ->withCount(['saleItems as sales_count' => function($query) {
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
    private function getHighStockProducts(int $tenantId, int $limit): Collection
    {
        return Product::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->with(['batches' => function($query) {
                $query->where('quantity', '>', 0)
                      ->where('expiry_date', '>', now())
                      ->orderBy('expiry_date');
            }])
            ->withSum('batches', 'quantity')
            ->whereHas('batches', function($query) {
                $query->where('quantity', '>', 0)
                      ->where('expiry_date', '>', now());
            })
            ->orderByDesc('batches_sum_quantity')
            ->limit($limit)
            ->get();
    }

    /**
     * Get nearly expired products (closest expiry dates first)
     */
    private function getNearlyExpiredProducts(int $tenantId, int $limit): Collection
    {
        $products = Product::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->with(['batches' => function($query) {
                $query->where('quantity', '>', 0)
                      ->where('expiry_date', '>', now())
                      ->where('expiry_date', '<=', now()->addDays(90))
                      ->orderBy('expiry_date');
            }])
            ->whereHas('batches', function($query) {
                $query->where('quantity', '>', 0)
                      ->where('expiry_date', '>', now())
                      ->where('expiry_date', '<=', now()->addDays(90));
            })
            ->get();

        $sorted = $products->sortBy(function($product) {
            return $product->batches->min('expiry_date');
        })->slice(0, $limit)->values();

        return $sorted;
    }

    /**
     * Get custom selected products in display order
     */
    private function getCustomSelectionProducts(int $tenantId, int $limit): Collection
    {
        $productIds = FeaturedProduct::forTenant($tenantId)
            ->limit($limit)
            ->pluck('product_id')
            ->toArray();

        if (empty($productIds)) {
            return collect();
        }

        $products = Product::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->with(['batches' => function($query) {
                $query->where('quantity', '>', 0)
                      ->where('expiry_date', '>', now())
                      ->orderBy('expiry_date');
            }])
            ->whereHas('batches', function($query) {
                $query->where('quantity', '>', 0)
                      ->where('expiry_date', '>', now());
            })
            ->whereIn('id', $productIds)
            ->get();

        // Sort by the order in featured_products
        $sorted = $products->sortBy(function($product) use ($productIds) {
            return array_search($product->id, $productIds);
        })->values();

        return $sorted;
    }
}

