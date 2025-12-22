<?php

namespace App\Exports;

use App\Models\Sale;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class SalesStatisticsExport implements FromArray, WithStyles, WithColumnWidths, WithTitle
{
    protected $startDate;
    protected $endDate;

    public function __construct($startDate, $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    /**
     * @return array
     */
    public function array(): array
    {
        // Calculate statistics
        $totalSales = Sale::where('status', 'completed')
            ->whereBetween('sale_date', [$this->startDate, $this->endDate])
            ->count();

        $totalRevenue = Sale::where('status', 'completed')
            ->whereBetween('sale_date', [$this->startDate, $this->endDate])
            ->sum('total_price');

        $averageOrderValue = $totalSales > 0 ? $totalRevenue / $totalSales : 0;

        // Top selling products
        $topProducts = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->where('sales.status', 'completed')
            ->whereBetween('sales.sale_date', [$this->startDate, $this->endDate])
            ->select('products.name', DB::raw('SUM(sale_items.quantity) as total_sold'), DB::raw('SUM(sale_items.total_price) as total_revenue'))
            ->groupBy('products.id', 'products.name')
            ->orderBy('total_sold', 'desc')
            ->limit(10)
            ->get();

        // Daily sales breakdown
        $dailySales = Sale::where('status', 'completed')
            ->whereBetween('sale_date', [$this->startDate, $this->endDate])
            ->selectRaw('DATE(sale_date) as date, COUNT(*) as sales_count, SUM(total_price) as daily_revenue')
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->get();

        $data = [
            ['SALES REPORT STATISTICS'],
            ['Period: ' . $this->startDate . ' to ' . $this->endDate],
            [''],
            ['SUMMARY STATISTICS'],
            ['Metric', 'Value'],
            ['Total Sales', $totalSales],
            ['Total Revenue', '$' . number_format($totalRevenue, 2)],
            ['Average Order Value', '$' . number_format($averageOrderValue, 2)],
            [''],
            ['TOP SELLING PRODUCTS'],
            ['Product Name', 'Quantity Sold', 'Revenue Generated'],
        ];

        foreach ($topProducts as $product) {
            $data[] = [
                trim($product->name ?? 'N/A'),
                (int) ($product->total_sold ?? 0),
                '$' . number_format((float) ($product->total_revenue ?? 0), 2)
            ];
        }

        $data[] = [''];
        $data[] = ['DAILY SALES BREAKDOWN'];
        $data[] = ['Date', 'Number of Sales', 'Daily Revenue'];

        foreach ($dailySales as $daily) {
            $data[] = [
                $daily->date ?? 'N/A',
                (int) ($daily->sales_count ?? 0),
                '$' . number_format((float) ($daily->daily_revenue ?? 0), 2)
            ];
        }

        return $data;
    }

    /**
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Main title
            1 => [
                'font' => [
                    'bold' => true,
                    'size' => 16,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '1565C0'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
            ],
            // Section headers
            4 => [
                'font' => ['bold' => true, 'size' => 12],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E3F2FD'],
                ],
            ],
            10 => [
                'font' => ['bold' => true, 'size' => 12],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E8F5E8'],
                ],
            ],
            // Table headers
            5 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'F5F5F5'],
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                    ],
                ],
            ],
            11 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'F5F5F5'],
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    public function columnWidths(): array
    {
        return [
            'A' => 25,
            'B' => 15,
            'C' => 20,
        ];
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'Statistics';
    }
}
