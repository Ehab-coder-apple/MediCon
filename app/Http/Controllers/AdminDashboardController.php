<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use App\Models\Batch;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Invoice;
use App\Models\Purchase;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $currencySymbol = SystemSetting::get('currency_symbol', '$');

        // Primary business KPIs (reusing existing reporting rules)
        $todaysSales = (float) Sale::where('status', 'completed')
            ->whereDate('sale_date', today())
            ->sum('total_price');

        $todaysProfit = $this->getProfitForRange(today()->startOfDay(), today()->endOfDay());

        $monthlySales = (float) Sale::where('status', 'completed')
            ->whereBetween('sale_date', [now()->startOfMonth(), now()->endOfMonth()])
            ->sum('total_price');

        $inventoryValue = (float) Batch::where('quantity', '>', 0)
            ->selectRaw('COALESCE(SUM(cost_price * quantity), 0) as value')
            ->value('value');

        // Inventory & finance alerts
        $lowStockProducts = $this->getLowStockProductsCount();
        $outOfStockProducts = $this->getOutOfStockProductsCount();
        $expiringSoonProducts = $this->getTotalNearlyExpiredProducts();
        $receivables = (float) Invoice::where('balance_due', '>', 0)->sum('balance_due');
        $payables = (float) Purchase::where('payment_status', '!=', 'paid')->sum('balance_due');

        // Inventory health breakdown (non-overlapping counts)
        $totalProducts = Product::where('is_active', true)->count();
        $healthyProducts = max($totalProducts - $lowStockProducts, 0);
        $lowOnlyProducts = max($lowStockProducts - $outOfStockProducts, 0);
        $inventoryHealth = [
            'total' => $totalProducts,
            'healthy' => $healthyProducts,
            'low' => $lowOnlyProducts,
            'out' => $outOfStockProducts,
        ];

        // Sales overview series (Today / 7 Days / 30 Days / This Year)
        $salesChart = $this->buildSalesSeries();

        // Top selling products for the current month
        $topProducts = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->where('sales.status', 'completed')
            ->whereBetween('sales.sale_date', [now()->startOfMonth(), now()->endOfMonth()])
            ->select('products.name', 'products.code', DB::raw('SUM(sale_items.quantity) as total_sold'), DB::raw('SUM(sale_items.total_price) as total_revenue'))
            ->groupBy('products.id', 'products.name', 'products.code')
            ->orderBy('total_sold', 'desc')
            ->limit(5)
            ->get();

        // Recent completed sales
        $recentSales = Sale::with('customer')
            ->where('status', 'completed')
            ->orderBy('sale_date', 'desc')
            ->orderBy('id', 'desc')
            ->limit(6)
            ->get();

        return view('admin.dashboard', compact(
            'currencySymbol',
            'todaysSales',
            'todaysProfit',
            'monthlySales',
            'inventoryValue',
            'lowStockProducts',
            'outOfStockProducts',
            'expiringSoonProducts',
            'receivables',
            'payables',
            'inventoryHealth',
            'salesChart',
            'topProducts',
            'recentSales'
        ));
    }

    /**
     * Calculate gross profit on goods sold within a date range.
     * Uses the same rule as ProfitAnalysisReportExport: (unit_price - product cost_price) * quantity
     * over completed sales.
     */
    private function getProfitForRange($start, $end): float
    {
        return (float) DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->where('sales.status', 'completed')
            ->whereBetween('sales.sale_date', [$start, $end])
            ->selectRaw('COALESCE(SUM((sale_items.unit_price - products.cost_price) * sale_items.quantity), 0) as profit')
            ->value('profit');
    }

    /**
     * Build the Sales Overview series for the four dashboard filters.
     * Buckets are computed in PHP to stay database-agnostic.
     */
    private function buildSalesSeries(): array
    {
        $yearSales = Sale::where('status', 'completed')
            ->whereBetween('sale_date', [now()->startOfYear(), now()->endOfYear()])
            ->get(['sale_date', 'created_at', 'total_price']);

        // Today - hourly buckets (by created_at time of sale)
        $todayLabels = [];
        $todayData = array_fill(0, 24, 0.0);
        for ($h = 0; $h < 24; $h++) {
            $todayLabels[] = sprintf('%02d:00', $h);
        }
        foreach ($yearSales as $sale) {
            if ($sale->created_at && $sale->created_at->isToday()) {
                $todayData[(int) $sale->created_at->format('G')] += (float) $sale->total_price;
            }
        }

        // 7 days and 30 days - daily buckets by sale_date
        $sevenLabels = $sevenData = $thirtyLabels = $thirtyData = [];
        $dailyTotals = [];
        foreach ($yearSales as $sale) {
            $key = $sale->sale_date?->format('Y-m-d');
            if ($key) {
                $dailyTotals[$key] = ($dailyTotals[$key] ?? 0) + (float) $sale->total_price;
            }
        }
        foreach ([7 => ['sevenLabels', 'sevenData'], 30 => ['thirtyLabels', 'thirtyData']] as $days => $vars) {
            for ($i = $days - 1; $i >= 0; $i--) {
                $date = now()->subDays($i);
                $key = $date->format('Y-m-d');
                ${$vars[0]}[] = $date->format('M j');
                ${$vars[1]}[] = round($dailyTotals[$key] ?? 0, 2);
            }
        }

        // This year - monthly buckets by sale_date
        $yearLabels = $yearData = [];
        $monthlyTotals = array_fill(1, 12, 0.0);
        foreach ($yearSales as $sale) {
            if ($sale->sale_date) {
                $monthlyTotals[(int) $sale->sale_date->format('n')] += (float) $sale->total_price;
            }
        }
        for ($m = 1; $m <= 12; $m++) {
            $yearLabels[] = now()->startOfYear()->addMonths($m - 1)->format('M');
            $yearData[] = round($monthlyTotals[$m], 2);
        }

        return [
            'today' => ['labels' => $todayLabels, 'data' => array_map(fn ($v) => round($v, 2), $todayData)],
            'week' => ['labels' => $sevenLabels, 'data' => $sevenData],
            'month' => ['labels' => $thirtyLabels, 'data' => $thirtyData],
            'year' => ['labels' => $yearLabels, 'data' => $yearData],
        ];
    }

    /**
     * Get count of products with expired batches
     */
    private function getTotalExpiredProducts(): int
    {
        return Product::whereHas('batches', function ($query) {
            $query->where('expiry_date', '<=', now());
        })->distinct()->count();
    }

    /**
     * Get count of products with batches expiring soon (within 30 days)
     */
    private function getTotalNearlyExpiredProducts(): int
    {
        return Product::whereHas('batches', function ($query) {
            $query->where('expiry_date', '>', now())
                  ->where('expiry_date', '<=', now()->addDays(30));
        })->distinct()->count();
    }

    /**
     * Get count of products with low stock (active quantity <= alert quantity)
     */
    private function getLowStockProductsCount(): int
    {
        $products = Product::with(['batches'])->get();
        return $products->filter(function ($product) {
            return $product->is_low_stock;
        })->count();
    }

    /**
     * Get count of products with zero quantity
     */
    private function getOutOfStockProductsCount(): int
    {
        $products = Product::with(['batches'])->get();
        return $products->filter(function ($product) {
            return $product->active_quantity == 0;
        })->count();
    }

    public function users()
    {
        $this->authorize('viewAny', User::class);

        $currentUser = auth()->user();

        $query = User::with('role');

        // For pharmacy/tenant admins, only show users that belong to their tenant
        // and hide system-level/super admin accounts (e.g. Program Owner)
        // as well as any users without an assigned role (legacy/system users).
        if (!$currentUser->is_super_admin) {
            $query->where('tenant_id', $currentUser->tenant_id)
                  ->where('is_super_admin', false)
                  ->whereNotNull('role_id');
        }

        $users = $query->paginate(10);

        return view('admin.users', compact('users'));
    }
}
