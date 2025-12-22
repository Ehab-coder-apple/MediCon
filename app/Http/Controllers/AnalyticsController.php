<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Supplier;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
class AnalyticsController extends Controller
{
    /**
     * Main analytics dashboard
     */
    public function dashboard(): View
    {
        // Check user permissions
        $user = auth()->user();
        $canViewAnalytics = $user->isAdmin() || $user->isPharmacist();

        if (!$canViewAnalytics) {
            abort(403, 'Access denied. Analytics are available to Admin and Pharmacist roles only.');
        }

        return view('analytics.dashboard');
    }

    /**
     * Product movement analytics
     */
    public function productMovement(): View
    {
        $this->authorize('access-admin-dashboard');

        return view('analytics.product-movement');
    }

    /**
     * Get product movement data for charts
     */
    public function getProductMovementData(Request $request): JsonResponse
    {
        $this->authorize('access-admin-dashboard');

        $days = $request->input('days', 30);
        $startDate = Carbon::now()->subDays($days);

        // Fast-moving products (top 10 by quantity sold)
        $fastMoving = Product::select('products.id', 'products.name', 'products.code')
            ->join('sale_items', 'products.id', '=', 'sale_items.product_id')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->where('sales.sale_date', '>=', $startDate)
            ->where('sales.status', 'completed')
            ->groupBy('products.id', 'products.name', 'products.code')
            ->selectRaw('SUM(sale_items.quantity) as total_sold')
            ->orderByDesc('total_sold')
            ->limit(10)
            ->get();

        // Slow-moving products (bottom 10 by quantity sold, excluding zero sales)
        $slowMoving = Product::select('products.id', 'products.name', 'products.code')
            ->leftJoin('sale_items', 'products.id', '=', 'sale_items.product_id')
            ->leftJoin('sales', function($join) use ($startDate) {
                $join->on('sale_items.sale_id', '=', 'sales.id')
                     ->where('sales.sale_date', '>=', $startDate)
                     ->where('sales.status', 'completed');
            })
            ->groupBy('products.id', 'products.name', 'products.code')
            ->selectRaw('COALESCE(SUM(sale_items.quantity), 0) as total_sold')
            ->havingRaw('total_sold > 0')
            ->orderBy('total_sold')
            ->limit(10)
            ->get();

        // No-movement products (zero sales in period)
        $noMovement = Product::select('products.id', 'products.name', 'products.code')
            ->leftJoin('sale_items', 'products.id', '=', 'sale_items.product_id')
            ->leftJoin('sales', function($join) use ($startDate) {
                $join->on('sale_items.sale_id', '=', 'sales.id')
                     ->where('sales.sale_date', '>=', $startDate)
                     ->where('sales.status', 'completed');
            })
            ->groupBy('products.id', 'products.name', 'products.code')
            ->havingRaw('COALESCE(SUM(sale_items.quantity), 0) = 0')
            ->limit(10)
            ->get()
            ->map(function($product) {
                $product->total_sold = 0;
                return $product;
            });

        return response()->json([
            'fast_moving' => $fastMoving,
            'slow_moving' => $slowMoving,
            'no_movement' => $noMovement,
            'period_days' => $days
        ]);
    }

    /**
     * Near-expiry alerts
     */
    public function expiryAlerts(): View
    {
        $user = auth()->user();
        $canViewAlerts = $user->isAdmin() || $user->isPharmacist();

        if (!$canViewAlerts) {
            abort(403, 'Access denied. Expiry alerts are available to Admin and Pharmacist roles only.');
        }

        return view('analytics.expiry-alerts');
    }

    /**
     * Get near-expiry data
     */
    public function getExpiryAlertsData(Request $request): JsonResponse
    {
        $user = auth()->user();
        $canViewAlerts = $user->isAdmin() || $user->isPharmacist();

        if (!$canViewAlerts) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $daysAhead = $request->input('days', 30);
        $alertDate = Carbon::now()->addDays($daysAhead);

        // Get batches expiring within the specified period
        $expiringBatches = Batch::with(['product'])
            ->where('expiry_date', '<=', $alertDate)
            ->where('expiry_date', '>', Carbon::now())
            ->where('quantity', '>', 0)
            ->orderBy('expiry_date')
            ->get()
            ->map(function($batch) {
                $daysToExpiry = Carbon::now()->diffInDays($batch->expiry_date, false);
                return [
                    'id' => $batch->id,
                    'product_name' => $batch->product->name,
                    'product_code' => $batch->product->code,
                    'batch_number' => $batch->batch_number,
                    'expiry_date' => $batch->expiry_date->format('Y-m-d'),
                    'expiry_date_formatted' => $batch->expiry_date->format('M d, Y'),
                    'quantity' => $batch->quantity,
                    'days_to_expiry' => $daysToExpiry,
                    'urgency' => $this->getExpiryUrgency($daysToExpiry),
                    'cost_value' => $batch->cost_price * $batch->quantity,
                ];
            });

        // Group by urgency
        $critical = $expiringBatches->where('urgency', 'critical'); // <= 7 days
        $warning = $expiringBatches->where('urgency', 'warning');   // 8-30 days
        $notice = $expiringBatches->where('urgency', 'notice');     // 31+ days

        // Calculate total value at risk
        $totalValueAtRisk = $expiringBatches->sum('cost_value');

        return response()->json([
            'critical' => $critical->values(),
            'warning' => $warning->values(),
            'notice' => $notice->values(),
            'total_batches' => $expiringBatches->count(),
            'total_value_at_risk' => $totalValueAtRisk,
            'alert_period_days' => $daysAhead
        ]);
    }

    /**
     * Sales analytics
     */
    public function salesAnalytics(): View
    {
        $user = auth()->user();
        $canViewSales = $user->isAdmin() || $user->isPharmacist() || $user->isSalesStaff();

        if (!$canViewSales) {
            abort(403, 'Access denied.');
        }

        return view('analytics.sales-analytics');
    }

    /**
     * Get sales analytics data
     */
    public function getSalesData(Request $request): JsonResponse
    {
        $user = auth()->user();
        $canViewSales = $user->isAdmin() || $user->isPharmacist() || $user->isSalesStaff();

        if (!$canViewSales) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $period = $request->input('period', 'month'); // day, week, month
        $days = $this->getPeriodDays($period);

        // Daily sales for the period
        $dailySales = Sale::where('sale_date', '>=', Carbon::now()->subDays($days))
            ->where('status', 'completed')
            ->selectRaw('DATE(sale_date) as date, SUM(total_price) as total, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Weekly sales (last 12 weeks)
        $weeklySales = Sale::where('sale_date', '>=', Carbon::now()->subWeeks(12))
            ->where('status', 'completed')
            ->selectRaw('YEAR(sale_date) as year, WEEK(sale_date) as week, SUM(total_price) as total, COUNT(*) as count')
            ->groupBy('year', 'week')
            ->orderBy('year')
            ->orderBy('week')
            ->get()
            ->map(function($sale) {
                $sale->week_start = Carbon::now()->setISODate($sale->year, $sale->week)->startOfWeek()->format('M d');
                return $sale;
            });

        // Monthly sales (last 12 months)
        $monthlySales = Sale::where('sale_date', '>=', Carbon::now()->subMonths(12))
            ->where('status', 'completed')
            ->selectRaw('YEAR(sale_date) as year, MONTH(sale_date) as month, SUM(total_price) as total, COUNT(*) as count')
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get()
            ->map(function($sale) {
                $sale->month_name = Carbon::createFromDate($sale->year, $sale->month, 1)->format('M Y');
                return $sale;
            });

        // Current period totals
        $currentPeriodTotal = Sale::where('sale_date', '>=', Carbon::now()->subDays($days))
            ->where('status', 'completed')
            ->sum('total_price');

        $currentPeriodCount = Sale::where('sale_date', '>=', Carbon::now()->subDays($days))
            ->where('status', 'completed')
            ->count();

        return response()->json([
            'daily_sales' => $dailySales,
            'weekly_sales' => $weeklySales,
            'monthly_sales' => $monthlySales,
            'current_period' => [
                'total' => $currentPeriodTotal,
                'count' => $currentPeriodCount,
                'average' => $currentPeriodCount > 0 ? $currentPeriodTotal / $currentPeriodCount : 0,
                'period' => $period,
                'days' => $days
            ]
        ]);
    }

    /**
     * Supplier analytics
     */
    public function supplierAnalytics(): View
    {
        $this->authorize('access-admin-dashboard');

        return view('analytics.supplier-analytics');
    }

    /**
     * Get supplier analytics data
     */
    public function getSupplierData(Request $request): JsonResponse
    {
        $this->authorize('access-admin-dashboard');

        $days = $request->input('days', 90);
        $startDate = Carbon::now()->subDays($days);

        // Top suppliers by purchase volume
        $topSuppliers = Supplier::select('suppliers.id', 'suppliers.name', 'suppliers.contact_person')
            ->join('purchases', 'suppliers.id', '=', 'purchases.supplier_id')
            ->where('purchases.purchase_date', '>=', $startDate)
            ->where('purchases.status', 'completed')
            ->groupBy('suppliers.id', 'suppliers.name', 'suppliers.contact_person')
            ->selectRaw('SUM(purchases.total_cost) as total_volume, COUNT(purchases.id) as purchase_count')
            ->orderByDesc('total_volume')
            ->limit(10)
            ->get();

        // Supplier performance metrics
        $supplierPerformance = Supplier::select('suppliers.id', 'suppliers.name')
            ->join('purchases', 'suppliers.id', '=', 'purchases.supplier_id')
            ->where('purchases.purchase_date', '>=', $startDate)
            ->groupBy('suppliers.id', 'suppliers.name')
            ->selectRaw('
                COUNT(purchases.id) as total_orders,
                SUM(purchases.total_cost) as total_value,
                AVG(purchases.total_cost) as avg_order_value,
                SUM(CASE WHEN purchases.status = "completed" THEN 1 ELSE 0 END) as completed_orders,
                SUM(CASE WHEN purchases.status = "pending" THEN 1 ELSE 0 END) as pending_orders
            ')
            ->having('total_orders', '>', 0)
            ->orderByDesc('total_value')
            ->get()
            ->map(function($supplier) {
                $supplier->completion_rate = $supplier->total_orders > 0
                    ? ($supplier->completed_orders / $supplier->total_orders) * 100
                    : 0;
                return $supplier;
            });

        // Purchase trends by supplier (monthly)
        $purchaseTrends = Purchase::select('supplier_id')
            ->with('supplier:id,name')
            ->where('purchase_date', '>=', Carbon::now()->subMonths(6))
            ->where('status', 'completed')
            ->selectRaw('
                suppliers.name as supplier_name,
                YEAR(purchase_date) as year,
                MONTH(purchase_date) as month,
                SUM(total_cost) as monthly_total,
                COUNT(*) as monthly_count
            ')
            ->join('suppliers', 'purchases.supplier_id', '=', 'suppliers.id')
            ->groupBy('supplier_id', 'suppliers.name', 'year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get()
            ->groupBy('supplier_name');

        return response()->json([
            'top_suppliers' => $topSuppliers,
            'supplier_performance' => $supplierPerformance,
            'purchase_trends' => $purchaseTrends,
            'period_days' => $days
        ]);
    }

    /**
     * Helper method to get expiry urgency level
     */
    private function getExpiryUrgency(int $daysToExpiry): string
    {
        if ($daysToExpiry <= 7) {
            return 'critical';
        } elseif ($daysToExpiry <= 30) {
            return 'warning';
        } else {
            return 'notice';
        }
    }

    /**
     * Get quick stats for dashboard
     */
    public function getQuickStats(): JsonResponse
    {
        $user = auth()->user();
        $canViewAnalytics = $user->isAdmin() || $user->isPharmacist();

        if (!$canViewAnalytics) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        // Today's sales
        $todaySales = Sale::where('sale_date', today())
            ->where('status', 'completed')
            ->sum('total_price');

        // Products in stock
        $productsInStock = Product::whereHas('batches', function($q) {
            $q->where('quantity', '>', 0);
        })->count();

        // Expiry alerts (next 30 days)
        $expiryAlerts = Batch::where('expiry_date', '<=', Carbon::now()->addDays(30))
            ->where('expiry_date', '>', Carbon::now())
            ->where('quantity', '>', 0)
            ->count();

        // Active suppliers (with purchases in last 90 days)
        $activeSuppliers = Supplier::whereHas('purchases', function($q) {
            $q->where('purchase_date', '>=', Carbon::now()->subDays(90));
        })->count();

        return response()->json([
            'today_sales' => $todaySales,
            'products_in_stock' => $productsInStock,
            'expiry_alerts' => $expiryAlerts,
            'active_suppliers' => $activeSuppliers
        ]);
    }

    /**
     * Helper method to get period days
     */
    private function getPeriodDays(string $period): int
    {
        return match($period) {
            'day' => 7,
            'week' => 30,
            'month' => 90,
            default => 30
        };
    }
}
