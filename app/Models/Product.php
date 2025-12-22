<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'tenant_id',
        'name',
        'category',
        'manufacturer',
        'code',
        'barcode',
        'cost_price',
        'selling_price',
        'alert_quantity',
        'days_on_hand',
        'description',
        'is_active',
        'category_id',
        'subcategory_id',
        'location_id',
    ];

    protected $casts = [
        'cost_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Get all batches for this product
     */
    public function batches(): HasMany
    {
        return $this->hasMany(Batch::class);
    }

    /**
     * Get all warehouse stock records for this product
     */
    public function warehouseStocks(): HasMany
    {
        return $this->hasMany(WarehouseStock::class);
    }


    /**
     * Get active batches (not expired and with quantity > 0)
     */
    public function activeBatches(): HasMany
    {
        return $this->hasMany(Batch::class)
            ->where('quantity', '>', 0)
            ->where('expiry_date', '>', now());
    }

    /**
     * Get total quantity across all batches
     */
    public function getTotalQuantityAttribute(): int
    {
        return $this->batches()->sum('quantity');
    }

    /**
     * Get total quantity available in sellable (On Shelf) warehouses
     * for the current tenant/branch context.
     */
    public function getOnShelfQuantityAttribute(): int
    {
        $user = auth()->user();

        // Try to get tenant ID from user, product, or app container
        $tenantId = $user?->tenant_id ?? $this->tenant_id;

        // Only try to get from app container if we don't have a tenant ID yet
        if (! $tenantId) {
            try {
                $tenantId = app('current_tenant')?->id;
            } catch (\Exception $e) {
                // If current_tenant is not bound, just use the product's tenant_id
                $tenantId = $this->tenant_id;
            }
        }

        $branchId = $user?->branch_id ?? null;

        if (! $tenantId) {
            // Fallback to legacy behavior if no tenant context is available
            return $this->active_quantity;
        }

        // Ensure default system warehouses (including On Shelf) exist
        Warehouse::ensureDefaultSystemWarehouses($tenantId, $branchId);

        $sellableWarehouseIds = Warehouse::sellable()
            ->forTenantAndBranch($tenantId, $branchId)
            ->pluck('id');

        if ($sellableWarehouseIds->isEmpty()) {
            return 0;
        }

        return (int) $this->warehouseStocks()
            ->whereIn('warehouse_id', $sellableWarehouseIds)
            ->sum('quantity');
    }


    /**
     * Get total active quantity (non-expired batches)
     */
    public function getActiveQuantityAttribute(): int
    {
        return $this->activeBatches()->sum('quantity');
    }

    /**
     * Check if product is low in stock
     */
    public function getIsLowStockAttribute(): bool
    {
        return $this->active_quantity <= $this->alert_quantity;
    }

    /**
     * Get expired batches
     */
    public function expiredBatches(): HasMany
    {
        return $this->hasMany(Batch::class)
            ->where('expiry_date', '<=', now());
    }

    /**
     * Get batches expiring soon (within 30 days)
     */
    public function expiringSoonBatches(): HasMany
    {
        return $this->hasMany(Batch::class)
            ->where('expiry_date', '>', now())
            ->where('expiry_date', '<=', now()->addDays(30));
    }

    /**
     * Get sale items for this product
     */
    public function saleItems(): HasMany
    {
        return $this->hasMany(\App\Models\SaleItem::class);
    }

    /**
     * Get invoice items for this product
     */
    public function invoiceItems(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    /**
     * Get the category that owns this product
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the subcategory that owns this product
     */
    public function subcategory()
    {
        return $this->belongsTo(Subcategory::class);
    }

    /**
     * Get the location that owns this product
     */
    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * Get the product information (AI/pharmaceutical data)
     */
    public function information()
    {
        return $this->hasOne(\App\Models\ProductInformation::class);
    }

    /**
     * Calculate average daily sales over the last 30 days
     */
    public function getAverageDailySalesAttribute(): float
    {
        $thirtyDaysAgo = now()->subDays(30);

        $totalSold = $this->saleItems()
            ->whereHas('sale', function ($query) use ($thirtyDaysAgo) {
                $query->where('sale_date', '>=', $thirtyDaysAgo)
                      ->where('status', 'completed');
            })
            ->sum('quantity');

        return $totalSold / 30; // Average per day over 30 days
    }

    /**
     * Calculate Days On Hand based on current stock and average daily sales
     */
    public function calculateDaysOnHand(): int
    {
        $currentStock = $this->active_quantity;
        $averageDailySales = $this->average_daily_sales;

        if ($averageDailySales <= 0) {
            // If no sales data, return a high number or null
            return $currentStock > 0 ? 999 : 0;
        }

        return (int) ceil($currentStock / $averageDailySales);
    }

    /**
     * Get calculated Days On Hand (dynamic calculation)
     */
    public function getCalculatedDaysOnHandAttribute(): int
    {
        return $this->calculateDaysOnHand();
    }

    /**
     * Update the stored Days On Hand value
     */
    public function updateDaysOnHand(): void
    {
        $this->update(['days_on_hand' => $this->calculateDaysOnHand()]);
    }

    /**
     * Get DOH status color for UI
     */
    public function getDohStatusAttribute(): string
    {
        $doh = $this->days_on_hand ?? $this->calculated_days_on_hand;

        if ($doh <= 7) {
            return 'critical'; // Red - Critical low
        } elseif ($doh <= 14) {
            return 'warning'; // Yellow - Warning
        } elseif ($doh <= 30) {
            return 'good'; // Green - Good
        } else {
            return 'excellent'; // Blue - Excellent
        }
    }

    /**
     * Get DOH status text for UI
     */
    public function getDohStatusTextAttribute(): string
    {
        $status = $this->doh_status;

        return match($status) {
            'critical' => 'Critical Low',
            'warning' => 'Low Stock',
            'good' => 'Good Stock',
            'excellent' => 'Overstocked',
            default => 'Unknown'
        };
    }

    /**
     * Calculate net price (cost price adjusted for bonus quantities)
     */
    public function getNetPriceAttribute(): float
    {
        // Get all stock receiving items for this product
        $stockItems = \App\Models\StockReceivingItem::where('product_id', $this->id)->get();

        if ($stockItems->isEmpty()) {
            return $this->cost_price;
        }

        $totalPaidQuantity = $stockItems->sum('quantity');
        $totalBonusQuantity = $stockItems->sum('bonus_quantity');
        $totalQuantityReceived = $totalPaidQuantity + $totalBonusQuantity;
        $totalCostPaid = $stockItems->sum(function($item) {
            return $item->quantity * $item->cost_price;
        });

        // If we have received quantities, calculate effective net price
        if ($totalQuantityReceived > 0) {
            return $totalCostPaid / $totalQuantityReceived;
        }

        return $this->cost_price;
    }

    /**
     * Get profit margin based on net price
     */
    public function getNetProfitMarginAttribute(): float
    {
        if ($this->net_price <= 0) {
            return 0;
        }

        return (($this->selling_price - $this->net_price) / $this->net_price) * 100;
    }

    /**
     * Get profit margin based on cost price
     */
    public function getCostProfitMarginAttribute(): float
    {
        if ($this->cost_price <= 0) {
            return 0;
        }

        return (($this->selling_price - $this->cost_price) / $this->cost_price) * 100;
    }

    /**
     * Get profit per unit based on net price
     */
    public function getNetProfitPerUnitAttribute(): float
    {
        return $this->selling_price - $this->net_price;
    }

    /**
     * Get profit per unit based on cost price
     */
    public function getCostProfitPerUnitAttribute(): float
    {
        return $this->selling_price - $this->cost_price;
    }
}
