<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use App\Models\Purchase;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\Batch;
use App\Models\User;
use App\Traits\HasRoleBasedRouting;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SalesReportExport;
use App\Exports\DetailedSalesReportExport;
use App\Exports\ComprehensiveSalesReportExport;
use App\Exports\ProfitAnalysisReportExport;

class ReportsController extends Controller
{
    use HasRoleBasedRouting;

    /**
     * Display the main reports dashboard
     */
    public function index(): View
    {
        $this->authorize('access-admin-dashboard');

        // Get summary statistics for the dashboard
        $stats = [
            'total_sales' => Sale::where('status', 'completed')->count(),
            'total_revenue' => Sale::where('status', 'completed')->sum('total_price'),
            'total_products' => Product::where('is_active', true)->count(),
            'total_customers' => Customer::count(),
            'low_stock_items' => Product::whereRaw('(SELECT SUM(quantity) FROM batches WHERE product_id = products.id) <= alert_quantity')->count(),
            'expired_items' => Batch::where('expiry_date', '<', now())->count(),
        ];

        return view('reports.index', compact('stats'));
    }

    /**
     * Sales Report
     */
    public function sales(Request $request): View
    {
        $this->authorize('access-admin-dashboard');

        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->endOfMonth()->format('Y-m-d'));
        $period = $request->input('period', 'daily');

        $sales = Sale::with(['customer', 'user', 'saleItems.product'])
            ->where('status', 'completed')
            ->whereBetween('sale_date', [$startDate, $endDate])
            ->orderBy('sale_date', 'desc')
            ->paginate(20);

        // Calculate summary statistics
        $totalSales = Sale::where('status', 'completed')
            ->whereBetween('sale_date', [$startDate, $endDate])
            ->count();

        $totalRevenue = Sale::where('status', 'completed')
            ->whereBetween('sale_date', [$startDate, $endDate])
            ->sum('total_price');

        $averageOrderValue = $totalSales > 0 ? $totalRevenue / $totalSales : 0;

        // Top selling products
        $topProducts = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->where('sales.status', 'completed')
            ->whereBetween('sales.sale_date', [$startDate, $endDate])
            ->select('products.name', 'products.code', DB::raw('SUM(sale_items.quantity) as total_sold'), DB::raw('SUM(sale_items.total_price) as total_revenue'))
            ->groupBy('products.id', 'products.name', 'products.code')
            ->orderBy('total_sold', 'desc')
            ->limit(10)
            ->get();

        $summary = [
            'total_sales' => $totalSales,
            'total_revenue' => $totalRevenue,
            'average_order_value' => $averageOrderValue,
            'top_products' => $topProducts
        ];

        return view('reports.sales', compact('sales', 'summary', 'startDate', 'endDate', 'period'));
    }

    /**
     * Inventory Report
     */
    public function inventory(Request $request): View
    {
        $this->authorize('access-admin-dashboard');

        $category = $request->input('category');
        $status = $request->input('status', 'all');

        $query = Product::with(['batches' => function($q) {
            $q->orderBy('expiry_date');
        }]);

        if ($category) {
            $query->where('category', $category);
        }

        if ($status === 'low_stock') {
            $query->whereRaw('(SELECT SUM(quantity) FROM batches WHERE product_id = products.id) <= alert_quantity');
        } elseif ($status === 'out_of_stock') {
            $query->whereRaw('(SELECT SUM(quantity) FROM batches WHERE product_id = products.id) = 0');
        } elseif ($status === 'expired') {
            $query->whereHas('batches', function($q) {
                $q->where('expiry_date', '<', now());
            });
        } elseif ($status === 'nearly_expired') {
            $query->whereHas('batches', function($q) {
                $q->where('expiry_date', '>', now())
                  ->where('expiry_date', '<=', now()->addDays(30))
                  ->where('quantity', '>', 0);
            });
        }

        $products = $query->paginate(20);

        // Get categories for filter
        $categories = Product::distinct()->pluck('category')->filter();

        // Summary statistics
        $totalProducts = Product::where('is_active', true)->count();
        $lowStockCount = Product::whereRaw('(SELECT SUM(quantity) FROM batches WHERE product_id = products.id) <= alert_quantity')->count();
        $outOfStockCount = Product::whereRaw('(SELECT SUM(quantity) FROM batches WHERE product_id = products.id) = 0')->count();
        $expiredBatchesCount = Batch::where('expiry_date', '<', now())->count();
        $nearlyExpiredCount = Batch::where('expiry_date', '>', now())
            ->where('expiry_date', '<=', now()->addDays(30))
            ->where('quantity', '>', 0)
            ->count();

        $summary = [
            'total_products' => $totalProducts,
            'low_stock_count' => $lowStockCount,
            'out_of_stock_count' => $outOfStockCount,
            'expired_batches_count' => $expiredBatchesCount,
            'nearly_expired_count' => $nearlyExpiredCount
        ];

        return view('reports.inventory', compact('products', 'categories', 'category', 'status', 'summary'));
    }

    /**
     * Customer Report
     */
    public function customers(Request $request): View
    {
        $this->authorize('access-admin-dashboard');

        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->endOfMonth()->format('Y-m-d'));

        $customers = Customer::whereHas('sales', function($query) use ($startDate, $endDate) {
            $query->where('status', 'completed')
                  ->whereBetween('sale_date', [$startDate, $endDate]);
        })
        ->withCount(['sales' => function($query) use ($startDate, $endDate) {
            $query->where('status', 'completed')
                  ->whereBetween('sale_date', [$startDate, $endDate]);
        }])
        ->with(['sales' => function($query) use ($startDate, $endDate) {
            $query->where('status', 'completed')
                  ->whereBetween('sale_date', [$startDate, $endDate]);
        }])
        ->orderBy('sales_count', 'desc')
        ->paginate(20);

        // Calculate customer statistics
        $totalCustomers = Customer::count();
        $activeCustomers = Customer::whereHas('sales', function($query) use ($startDate, $endDate) {
            $query->where('status', 'completed')
                  ->whereBetween('sale_date', [$startDate, $endDate]);
        })->count();

        $topCustomers = Customer::whereHas('sales', function($query) use ($startDate, $endDate) {
            $query->where('status', 'completed')
                  ->whereBetween('sale_date', [$startDate, $endDate]);
        })
        ->withCount(['sales' => function($query) use ($startDate, $endDate) {
            $query->where('status', 'completed')
                  ->whereBetween('sale_date', [$startDate, $endDate]);
        }])
        ->with(['sales' => function($query) use ($startDate, $endDate) {
            $query->where('status', 'completed')
                  ->whereBetween('sale_date', [$startDate, $endDate]);
        }])
        ->orderBy('sales_count', 'desc')
        ->limit(10)
        ->get();

        $summary = [
            'total_customers' => $totalCustomers,
            'active_customers' => $activeCustomers,
            'top_customers' => $topCustomers
        ];

        return view('reports.customers', compact('customers', 'summary', 'startDate', 'endDate'));
    }

    /**
     * Financial Report
     */
    public function financial(Request $request): View
    {
        $this->authorize('access-admin-dashboard');

        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->endOfMonth()->format('Y-m-d'));

        // Sales revenue
        $salesRevenue = Sale::where('status', 'completed')
            ->whereBetween('sale_date', [$startDate, $endDate])
            ->sum('total_price');

        // Purchase costs
        $purchaseCosts = Purchase::where('status', 'completed')
            ->whereBetween('purchase_date', [$startDate, $endDate])
            ->sum('total_cost');

        // Profit calculation (simplified)
        $grossProfit = $salesRevenue - $purchaseCosts;

        // Supplier payment tracking
        $totalUnpaidAmount = Purchase::where('payment_status', '!=', 'paid')->sum('balance_due');
        $overdueAmount = Purchase::overdue()->sum('balance_due');
        $unpaidPurchases = Purchase::with('supplier')
            ->where('payment_status', '!=', 'paid')
            ->where('balance_due', '>', 0)
            ->orderBy('due_date')
            ->get();

        // Monthly breakdown - Database agnostic
        $dateExtraction = $this->getDateExtractionSql();
        $monthlyData = Sale::where('status', 'completed')
            ->whereBetween('sale_date', [$startDate, $endDate])
            ->selectRaw("{$dateExtraction['year']} as year, {$dateExtraction['month']} as month, SUM(total_price) as revenue, COUNT(*) as sales_count")
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();

        $summary = [
            'sales_revenue' => $salesRevenue,
            'purchase_costs' => $purchaseCosts,
            'gross_profit' => $grossProfit,
            'profit_margin' => $salesRevenue > 0 ? ($grossProfit / $salesRevenue) * 100 : 0,
            'total_unpaid_amount' => $totalUnpaidAmount,
            'overdue_amount' => $overdueAmount,
            'unpaid_purchases' => $unpaidPurchases,
            'monthly_data' => $monthlyData
        ];

        return view('reports.financial', compact('summary', 'startDate', 'endDate'));
    }

    /**
     * Export sales report as CSV
     */
    public function exportSales(Request $request)
    {
        $this->authorize('access-admin-dashboard');

        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->endOfMonth()->format('Y-m-d'));

        $sales = Sale::with(['customer', 'user', 'saleItems.product'])
            ->where('status', 'completed')
            ->whereBetween('sale_date', [$startDate, $endDate])
            ->orderBy('sale_date', 'desc')
            ->get();

        $filename = 'sales_report_' . $startDate . '_to_' . $endDate . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0'
        ];

        $callback = function() use ($sales) {
            $file = fopen('php://output', 'w');

            // Set UTF-8 BOM for proper encoding
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            // CSV headers
            fputcsv($file, [
                'Invoice Number',
                'Date',
                'Customer',
                'Staff',
                'Items Count',
                'Total Amount',
                'Status'
            ], ',', '"');

            foreach ($sales as $sale) {
                // Clean and sanitize data
                $csvData = [
                    trim($sale->invoice_number ?? ''),
                    $sale->sale_date ? $sale->sale_date->format('Y-m-d') : '',
                    trim($sale->customer ? $sale->customer->name : 'Walk-in Customer'),
                    trim($sale->user->name ?? ''),
                    (string) $sale->saleItems->count(),
                    number_format($sale->total_price, 2),
                    ucfirst(trim($sale->status ?? 'unknown'))
                ];

                fputcsv($file, $csvData, ',', '"');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export sales report as Excel
     */
    public function exportSalesExcel(Request $request)
    {
        $this->authorize('access-admin-dashboard');

        // Increase memory limit for large exports
        ini_set('memory_limit', '512M');
        set_time_limit(300); // 5 minutes

        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->endOfMonth()->format('Y-m-d'));

        $filename = 'sales_report_' . $startDate . '_to_' . $endDate . '.xlsx';

        return Excel::download(new SalesReportExport($startDate, $endDate), $filename);
    }

    /**
     * Export detailed sales report as Excel (with individual items)
     */
    public function exportDetailedSalesExcel(Request $request)
    {
        $this->authorize('access-admin-dashboard');

        // Increase memory limit for large exports
        ini_set('memory_limit', '512M');
        set_time_limit(300); // 5 minutes

        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->endOfMonth()->format('Y-m-d'));

        $filename = 'detailed_sales_report_' . $startDate . '_to_' . $endDate . '.xlsx';

        return Excel::download(new DetailedSalesReportExport($startDate, $endDate), $filename);
    }

    /**
     * Export comprehensive sales report as Excel (multi-sheet with summary, details, and statistics)
     */
    public function exportComprehensiveSalesExcel(Request $request)
    {
        $this->authorize('access-admin-dashboard');

        try {
            // Increase memory limit for large exports
            ini_set('memory_limit', '512M');
            set_time_limit(300); // 5 minutes

            $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
            $endDate = $request->input('end_date', now()->endOfMonth()->format('Y-m-d'));

            $filename = 'comprehensive_sales_report_' . $startDate . '_to_' . $endDate . '.xlsx';

            return Excel::download(new ComprehensiveSalesReportExport($startDate, $endDate), $filename);
        } catch (\Exception $e) {
            \Log::error('Comprehensive Sales Export Error: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Failed to generate comprehensive sales report. Please try again.');
        }
    }

    /**
     * Export profit analysis report as Excel
     */
    public function exportProfitAnalysisExcel(Request $request)
    {
        $this->authorize('access-admin-dashboard');

        try {
            // Increase memory limit for large exports
            ini_set('memory_limit', '512M');
            set_time_limit(300); // 5 minutes

            $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
            $endDate = $request->input('end_date', now()->endOfMonth()->format('Y-m-d'));

            $filename = 'profit_analysis_report_' . $startDate . '_to_' . $endDate . '.xlsx';

            return Excel::download(new ProfitAnalysisReportExport($startDate, $endDate), $filename);
        } catch (\Exception $e) {
            \Log::error('Profit Analysis Export Error: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Failed to generate profit analysis report. Please try again.');
        }
    }

    /**
     * Get database-specific date extraction SQL
     */
    private function getDateExtractionSql(): array
    {
        $driver = config('database.default');
        $connection = config("database.connections.{$driver}.driver");

        if ($connection === 'sqlite') {
            return [
                'year' => "strftime('%Y', sale_date)",
                'month' => "strftime('%m', sale_date)",
                'day' => "strftime('%d', sale_date)",
            ];
        } else {
            // MySQL, PostgreSQL, etc.
            return [
                'year' => 'YEAR(sale_date)',
                'month' => 'MONTH(sale_date)',
                'day' => 'DAY(sale_date)',
            ];
        }
    }
}
