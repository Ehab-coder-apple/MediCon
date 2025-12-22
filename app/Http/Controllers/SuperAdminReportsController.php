<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\User;
use App\Models\Sale;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SuperAdminReportsController extends Controller
{
    /**
     * Show reports dashboard
     */
    public function index(): View
    {
        $companyMetrics = $this->getCompanyMetrics();
        $tenantMetrics = $this->getTenantMetrics();
        $recentActivity = $this->getRecentActivity();

        return view('super-admin.reports.index', compact(
            'companyMetrics',
            'tenantMetrics', 
            'recentActivity'
        ));
    }

    /**
     * Get company-wide business metrics
     */
    private function getCompanyMetrics(): array
    {
        $currentMonth = now()->startOfMonth();
        $lastMonth = now()->subMonth()->startOfMonth();
        $currentYear = now()->startOfYear();

        return [
            // Revenue Metrics
            'total_revenue' => $this->calculateTotalRevenue(),
            'monthly_revenue' => $this->calculateMonthlyRevenue($currentMonth),
            'yearly_revenue' => $this->calculateYearlyRevenue($currentYear),
            'revenue_growth' => $this->calculateRevenueGrowth($currentMonth, $lastMonth),

            // Tenant Metrics
            'total_tenants' => Tenant::count(),
            'active_tenants' => Tenant::where('is_active', true)->count(),
            'new_tenants_this_month' => Tenant::where('created_at', '>=', $currentMonth)->count(),
            'subscription_revenue' => $this->calculateSubscriptionRevenue(),

            // User Metrics
            'total_users' => User::where('is_super_admin', false)->count(),
            'active_users' => User::where('is_active', true)->where('is_super_admin', false)->count(),
            'new_users_this_month' => User::where('created_at', '>=', $currentMonth)->where('is_super_admin', false)->count(),

            // Platform Metrics
            'total_sales_transactions' => Sale::count(),
            'total_products_managed' => Product::count(),
            'total_customers' => Customer::count(),
            'platform_utilization' => $this->calculatePlatformUtilization(),
        ];
    }

    /**
     * Get tenant business metrics
     */
    private function getTenantMetrics(): array
    {
        $currentMonth = now()->startOfMonth();
        
        return [
            // Sales Performance
            'top_performing_tenants' => $this->getTopPerformingTenants(),
            'tenant_sales_summary' => $this->getTenantSalesSummary(),
            'category_performance' => $this->getCategoryPerformance(),
            'monthly_trends' => $this->getMonthlyTrends(),

            // Product Metrics
            'total_products_across_tenants' => Product::count(),
            'most_sold_products' => $this->getMostSoldProducts(),
            'inventory_insights' => $this->getInventoryInsights(),

            // Customer Metrics
            'total_customers_across_tenants' => Customer::count(),
            'customer_growth' => $this->getCustomerGrowth($currentMonth),
        ];
    }

    /**
     * Get recent platform activity
     */
    private function getRecentActivity(): array
    {
        return [
            'recent_sales' => Sale::with(['tenant', 'customer', 'user'])
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get(),
            'recent_tenants' => Tenant::orderBy('created_at', 'desc')
                ->limit(5)
                ->get(),
            'recent_users' => User::with(['tenant', 'role'])
                ->where('is_super_admin', false)
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get(),
        ];
    }

    /**
     * Calculate total platform revenue
     */
    private function calculateTotalRevenue(): float
    {
        // This would include subscription fees, transaction fees, etc.
        return Tenant::sum('subscription_amount') ?? 0;
    }

    /**
     * Calculate monthly revenue
     */
    private function calculateMonthlyRevenue(Carbon $month): float
    {
        return Tenant::where('created_at', '>=', $month)
            ->sum('subscription_amount') ?? 0;
    }

    /**
     * Calculate yearly revenue
     */
    private function calculateYearlyRevenue(Carbon $year): float
    {
        return Tenant::where('created_at', '>=', $year)
            ->sum('subscription_amount') ?? 0;
    }

    /**
     * Calculate revenue growth percentage
     */
    private function calculateRevenueGrowth(Carbon $currentMonth, Carbon $lastMonth): float
    {
        $currentRevenue = $this->calculateMonthlyRevenue($currentMonth);
        $lastRevenue = $this->calculateMonthlyRevenue($lastMonth);
        
        if ($lastRevenue == 0) return 0;
        
        return (($currentRevenue - $lastRevenue) / $lastRevenue) * 100;
    }

    /**
     * Calculate subscription revenue
     */
    private function calculateSubscriptionRevenue(): float
    {
        return Tenant::where('is_active', true)
            ->sum('subscription_amount') ?? 0;
    }

    /**
     * Calculate platform utilization
     */
    private function calculatePlatformUtilization(): float
    {
        $totalTenants = Tenant::count();
        $activeTenants = Tenant::where('is_active', true)->count();
        
        if ($totalTenants == 0) return 0;
        
        return ($activeTenants / $totalTenants) * 100;
    }

    /**
     * Get top performing tenants by sales
     */
    private function getTopPerformingTenants(): array
    {
        return DB::table('sales')
            ->join('tenants', 'sales.tenant_id', '=', 'tenants.id')
            ->select(
                'tenants.id',
                'tenants.name',
                'tenants.pharmacy_name',
                DB::raw('COUNT(sales.id) as total_sales'),
                DB::raw('SUM(sales.total_price) as total_revenue'),
                DB::raw('AVG(sales.total_price) as avg_sale_amount')
            )
            ->groupBy('tenants.id', 'tenants.name', 'tenants.pharmacy_name')
            ->orderBy('total_revenue', 'desc')
            ->limit(10)
            ->get()
            ->toArray();
    }

    /**
     * Get tenant sales summary
     */
    private function getTenantSalesSummary(): array
    {
        return [
            'total_sales_value' => Sale::sum('total_price') ?? 0,
            'total_sales_count' => Sale::count(),
            'average_sale_value' => Sale::avg('total_price') ?? 0,
            'sales_this_month' => Sale::where('created_at', '>=', now()->startOfMonth())->sum('total_price') ?? 0,
            'sales_growth' => $this->calculateSalesGrowth(),
        ];
    }

    /**
     * Calculate sales growth
     */
    private function calculateSalesGrowth(): float
    {
        $currentMonth = Sale::where('created_at', '>=', now()->startOfMonth())->sum('total_price') ?? 0;
        $lastMonth = Sale::whereBetween('created_at', [
            now()->subMonth()->startOfMonth(),
            now()->subMonth()->endOfMonth()
        ])->sum('total_price') ?? 0;

        if ($lastMonth == 0) return 0;

        return (($currentMonth - $lastMonth) / $lastMonth) * 100;
    }

    /**
     * Get category performance across all tenants
     */
    private function getCategoryPerformance(): array
    {
        return DB::table('sale_items')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->select(
                'products.category',
                DB::raw('COUNT(sale_items.id) as items_sold'),
                DB::raw('SUM(sale_items.quantity) as total_quantity'),
                DB::raw('SUM(sale_items.total_price) as total_revenue')
            )
            ->groupBy('products.category')
            ->orderBy('total_revenue', 'desc')
            ->get()
            ->toArray();
    }

    /**
     * Get monthly sales trends
     */
    private function getMonthlyTrends(): array
    {
        return DB::table('sales')
            ->select(
                DB::raw('strftime("%Y-%m", created_at) as month'),
                DB::raw('COUNT(*) as sales_count'),
                DB::raw('SUM(total_price) as total_revenue')
            )
            ->where('created_at', '>=', now()->subMonths(12))
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->toArray();
    }

    /**
     * Get most sold products across all tenants
     */
    private function getMostSoldProducts(): array
    {
        return DB::table('sale_items')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->join('tenants', 'products.tenant_id', '=', 'tenants.id')
            ->select(
                'products.id',
                'products.name',
                'products.category',
                'tenants.name as tenant_name',
                DB::raw('SUM(sale_items.quantity) as total_sold'),
                DB::raw('SUM(sale_items.total_price) as total_revenue')
            )
            ->groupBy('products.id', 'products.name', 'products.category', 'tenants.name')
            ->orderBy('total_sold', 'desc')
            ->limit(20)
            ->get()
            ->toArray();
    }

    /**
     * Get inventory insights
     */
    private function getInventoryInsights(): array
    {
        // Get products with low stock (sum of all batches <= alert_quantity)
        $lowStockProducts = DB::table('products')
            ->leftJoin('batches', 'products.id', '=', 'batches.product_id')
            ->select('products.id', 'products.alert_quantity', DB::raw('COALESCE(SUM(batches.quantity), 0) as total_quantity'))
            ->groupBy('products.id', 'products.alert_quantity')
            ->havingRaw('total_quantity <= products.alert_quantity')
            ->count();

        // Get products with no stock
        $outOfStockProducts = DB::table('products')
            ->leftJoin('batches', 'products.id', '=', 'batches.product_id')
            ->select('products.id', DB::raw('COALESCE(SUM(batches.quantity), 0) as total_quantity'))
            ->groupBy('products.id')
            ->havingRaw('total_quantity = 0')
            ->count();

        // Calculate total inventory value
        $totalInventoryValue = DB::table('batches')
            ->selectRaw('SUM(quantity * cost_price)')
            ->value('SUM(quantity * cost_price)') ?? 0;

        return [
            'total_products' => Product::count(),
            'low_stock_products' => $lowStockProducts,
            'out_of_stock_products' => $outOfStockProducts,
            'total_inventory_value' => $totalInventoryValue,
        ];
    }

    /**
     * Get customer growth metrics
     */
    private function getCustomerGrowth(Carbon $currentMonth): array
    {
        $lastMonth = $currentMonth->copy()->subMonth();
        
        return [
            'total_customers' => Customer::count(),
            'new_customers_this_month' => Customer::where('created_at', '>=', $currentMonth)->count(),
            'new_customers_last_month' => Customer::whereBetween('created_at', [
                $lastMonth->startOfMonth(),
                $lastMonth->endOfMonth()
            ])->count(),
            'customer_growth_rate' => $this->calculateCustomerGrowthRate($currentMonth, $lastMonth),
        ];
    }

    /**
     * Calculate customer growth rate
     */
    private function calculateCustomerGrowthRate(Carbon $currentMonth, Carbon $lastMonth): float
    {
        $currentCount = Customer::where('created_at', '>=', $currentMonth)->count();
        $lastCount = Customer::whereBetween('created_at', [
            $lastMonth->startOfMonth(),
            $lastMonth->endOfMonth()
        ])->count();
        
        if ($lastCount == 0) return 0;
        
        return (($currentCount - $lastCount) / $lastCount) * 100;
    }

    /**
     * Get detailed tenant report
     */
    public function tenantDetails(Request $request): JsonResponse
    {
        $tenantId = $request->get('tenant_id');
        $dateRange = $request->get('date_range', '30'); // days
        
        $tenant = Tenant::findOrFail($tenantId);
        $startDate = now()->subDays($dateRange);
        
        $data = [
            'tenant_info' => $tenant,
            'sales_metrics' => $this->getTenantSalesMetrics($tenantId, $startDate),
            'product_metrics' => $this->getTenantProductMetrics($tenantId),
            'customer_metrics' => $this->getTenantCustomerMetrics($tenantId, $startDate),
            'inventory_metrics' => $this->getTenantInventoryMetrics($tenantId),
        ];
        
        return response()->json($data);
    }

    /**
     * Get tenant-specific sales metrics
     */
    private function getTenantSalesMetrics(int $tenantId, Carbon $startDate): array
    {
        return [
            'total_sales' => Sale::where('tenant_id', $tenantId)->sum('total_price') ?? 0,
            'sales_count' => Sale::where('tenant_id', $tenantId)->count(),
            'recent_sales' => Sale::where('tenant_id', $tenantId)
                ->where('created_at', '>=', $startDate)
                ->sum('total_price') ?? 0,
            'average_sale' => Sale::where('tenant_id', $tenantId)->avg('total_price') ?? 0,
            'daily_sales' => $this->getTenantDailySales($tenantId, $startDate),
        ];
    }

    /**
     * Get tenant daily sales for chart
     */
    private function getTenantDailySales(int $tenantId, Carbon $startDate): array
    {
        return DB::table('sales')
            ->where('tenant_id', $tenantId)
            ->where('created_at', '>=', $startDate)
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as sales_count'),
                DB::raw('SUM(total_price) as total_amount')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->toArray();
    }

    /**
     * Get tenant product metrics
     */
    private function getTenantProductMetrics(int $tenantId): array
    {
        // Get products with stock (have batches with quantity > 0)
        $activeProducts = DB::table('products')
            ->leftJoin('batches', 'products.id', '=', 'batches.product_id')
            ->where('products.tenant_id', $tenantId)
            ->select('products.id', DB::raw('COALESCE(SUM(batches.quantity), 0) as total_quantity'))
            ->groupBy('products.id')
            ->havingRaw('total_quantity > 0')
            ->count();

        // Get products with low stock
        $lowStockProducts = DB::table('products')
            ->leftJoin('batches', 'products.id', '=', 'batches.product_id')
            ->where('products.tenant_id', $tenantId)
            ->select('products.id', 'products.alert_quantity', DB::raw('COALESCE(SUM(batches.quantity), 0) as total_quantity'))
            ->groupBy('products.id', 'products.alert_quantity')
            ->havingRaw('total_quantity <= products.alert_quantity')
            ->count();

        return [
            'total_products' => Product::where('tenant_id', $tenantId)->count(),
            'active_products' => $activeProducts,
            'low_stock_products' => $lowStockProducts,
            'categories' => Product::where('tenant_id', $tenantId)
                ->distinct('category')->count('category'),
        ];
    }

    /**
     * Get tenant customer metrics
     */
    private function getTenantCustomerMetrics(int $tenantId, Carbon $startDate): array
    {
        return [
            'total_customers' => Customer::where('tenant_id', $tenantId)->count(),
            'new_customers' => Customer::where('tenant_id', $tenantId)
                ->where('created_at', '>=', $startDate)->count(),
            'repeat_customers' => $this->getTenantRepeatCustomers($tenantId),
        ];
    }

    /**
     * Get tenant repeat customers
     */
    private function getTenantRepeatCustomers(int $tenantId): int
    {
        return DB::table('sales')
            ->where('tenant_id', $tenantId)
            ->whereNotNull('customer_id')
            ->groupBy('customer_id')
            ->havingRaw('COUNT(*) > 1')
            ->count();
    }

    /**
     * Get tenant inventory metrics
     */
    private function getTenantInventoryMetrics(int $tenantId): array
    {
        // Calculate inventory value from batches
        $inventoryValue = DB::table('batches')
            ->join('products', 'batches.product_id', '=', 'products.id')
            ->where('products.tenant_id', $tenantId)
            ->selectRaw('SUM(batches.quantity * batches.cost_price)')
            ->value('SUM(batches.quantity * batches.cost_price)') ?? 0;

        return [
            'inventory_value' => $inventoryValue,
            'total_purchases' => Purchase::where('tenant_id', $tenantId)->sum('total_cost') ?? 0,
            'purchase_count' => Purchase::where('tenant_id', $tenantId)->count(),
        ];
    }

    /**
     * Export reports data
     */
    public function exportReports(Request $request): JsonResponse
    {
        $format = $request->get('format', 'json'); // json, csv, pdf
        $reportType = $request->get('type', 'summary'); // summary, detailed, tenant_specific

        try {
            $data = $this->getExportData($reportType, $request);

            switch ($format) {
                case 'csv':
                    return $this->exportToCsv($data, $reportType);
                case 'pdf':
                    return $this->exportToPdf($data, $reportType);
                default:
                    return response()->json($data);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get data for export
     */
    private function getExportData(string $reportType, Request $request): array
    {
        switch ($reportType) {
            case 'detailed':
                return [
                    'company_metrics' => $this->getCompanyMetrics(),
                    'tenant_metrics' => $this->getTenantMetrics(),
                    'tenant_details' => $this->getAllTenantDetails(),
                ];
            case 'tenant_specific':
                $tenantId = $request->get('tenant_id');
                return $this->getTenantSpecificData($tenantId);
            default:
                return [
                    'summary' => $this->getCompanyMetrics(),
                    'top_tenants' => $this->getTopPerformingTenants(),
                    'generated_at' => now()->toISOString(),
                ];
        }
    }

    /**
     * Get all tenant details for export
     */
    private function getAllTenantDetails(): array
    {
        return Tenant::with(['users', 'products', 'sales'])
            ->get()
            ->map(function ($tenant) {
                return [
                    'id' => $tenant->id,
                    'name' => $tenant->name,
                    'pharmacy_name' => $tenant->pharmacy_name,
                    'subscription_plan' => $tenant->subscription_plan,
                    'subscription_amount' => $tenant->subscription_amount,
                    'total_users' => $tenant->users->count(),
                    'total_products' => $tenant->products->count(),
                    'total_sales' => $tenant->sales->sum('total_price'),
                    'sales_count' => $tenant->sales->count(),
                    'created_at' => $tenant->created_at,
                    'is_active' => $tenant->is_active,
                ];
            })
            ->toArray();
    }

    /**
     * Get tenant specific data
     */
    private function getTenantSpecificData(int $tenantId): array
    {
        $tenant = Tenant::findOrFail($tenantId);

        return [
            'tenant_info' => $tenant,
            'sales_data' => Sale::where('tenant_id', $tenantId)->with(['customer', 'saleItems.product'])->get(),
            'products_data' => Product::where('tenant_id', $tenantId)->get(),
            'customers_data' => Customer::where('tenant_id', $tenantId)->get(),
            'users_data' => User::where('tenant_id', $tenantId)->with('role')->get(),
        ];
    }

    /**
     * Export to CSV
     */
    private function exportToCsv(array $data, string $reportType): JsonResponse
    {
        // This would generate CSV content
        // For now, return JSON with CSV flag
        return response()->json([
            'format' => 'csv',
            'data' => $data,
            'download_url' => '/super-admin/reports/download/csv/' . time(),
        ]);
    }

    /**
     * Export to PDF
     */
    private function exportToPdf(array $data, string $reportType): JsonResponse
    {
        // This would generate PDF content
        // For now, return JSON with PDF flag
        return response()->json([
            'format' => 'pdf',
            'data' => $data,
            'download_url' => '/super-admin/reports/download/pdf/' . time(),
        ]);
    }

    /**
     * Get financial overview
     */
    public function financialOverview(): JsonResponse
    {
        $data = [
            'revenue_breakdown' => $this->getRevenueBreakdown(),
            'expense_tracking' => $this->getExpenseTracking(),
            'profit_margins' => $this->getProfitMargins(),
            'subscription_analytics' => $this->getSubscriptionAnalytics(),
        ];

        return response()->json($data);
    }

    /**
     * Get revenue breakdown
     */
    private function getRevenueBreakdown(): array
    {
        return [
            'subscription_revenue' => Tenant::where('is_active', true)->sum('subscription_amount'),
            'transaction_fees' => $this->calculateTransactionFees(),
            'setup_fees' => $this->calculateSetupFees(),
            'total_monthly_recurring' => $this->calculateMRR(),
        ];
    }

    /**
     * Calculate transaction fees (example: 2% of all sales)
     */
    private function calculateTransactionFees(): float
    {
        return Sale::sum('total_price') * 0.02; // 2% transaction fee
    }

    /**
     * Calculate setup fees
     */
    private function calculateSetupFees(): float
    {
        // Example: $50 setup fee per new tenant this month
        $newTenants = Tenant::where('created_at', '>=', now()->startOfMonth())->count();
        return $newTenants * 50;
    }

    /**
     * Calculate Monthly Recurring Revenue
     */
    private function calculateMRR(): float
    {
        return Tenant::where('is_active', true)->sum('subscription_amount');
    }

    /**
     * Get expense tracking (placeholder)
     */
    private function getExpenseTracking(): array
    {
        return [
            'server_costs' => 2500.00,
            'staff_salaries' => 15000.00,
            'marketing' => 3000.00,
            'support' => 1500.00,
            'total_monthly_expenses' => 22000.00,
        ];
    }

    /**
     * Get profit margins
     */
    private function getProfitMargins(): array
    {
        $revenue = $this->calculateMRR();
        $expenses = 22000.00; // From getExpenseTracking
        $profit = $revenue - $expenses;

        return [
            'gross_revenue' => $revenue,
            'total_expenses' => $expenses,
            'net_profit' => $profit,
            'profit_margin' => $revenue > 0 ? ($profit / $revenue) * 100 : 0,
        ];
    }

    /**
     * Get subscription analytics
     */
    private function getSubscriptionAnalytics(): array
    {
        return [
            'total_subscriptions' => Tenant::count(),
            'active_subscriptions' => Tenant::where('is_active', true)->count(),
            'churn_rate' => $this->calculateChurnRate(),
            'plan_distribution' => $this->getPlanDistribution(),
            'ltv' => $this->calculateLifetimeValue(),
        ];
    }

    /**
     * Calculate churn rate
     */
    private function calculateChurnRate(): float
    {
        $startOfMonth = now()->startOfMonth();
        $activeStart = Tenant::where('is_active', true)->where('created_at', '<', $startOfMonth)->count();
        $churned = Tenant::where('is_active', false)->where('updated_at', '>=', $startOfMonth)->count();

        return $activeStart > 0 ? ($churned / $activeStart) * 100 : 0;
    }

    /**
     * Get plan distribution
     */
    private function getPlanDistribution(): array
    {
        return Tenant::selectRaw('subscription_plan, COUNT(*) as count, SUM(subscription_amount) as revenue')
            ->groupBy('subscription_plan')
            ->get()
            ->toArray();
    }

    /**
     * Calculate average customer lifetime value
     */
    private function calculateLifetimeValue(): float
    {
        $avgMonthlyRevenue = Tenant::where('is_active', true)->avg('subscription_amount') ?? 0;
        $avgLifetimeMonths = 24; // Assume 24 months average lifetime

        return $avgMonthlyRevenue * $avgLifetimeMonths;
    }
}
