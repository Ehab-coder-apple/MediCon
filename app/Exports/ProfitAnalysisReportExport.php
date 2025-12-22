<?php

namespace App\Exports;

use App\Models\Sale;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ProfitAnalysisReportExport implements FromQuery, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithTitle, ShouldAutoSize, WithChunkReading
{
    protected $startDate;
    protected $endDate;

    public function __construct($startDate, $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    /**
     * @return \Illuminate\Database\Query\Builder
     */
    public function query()
    {
        return \App\Models\SaleItem::query()
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->leftJoin('customers', 'sales.customer_id', '=', 'customers.id')
            ->leftJoin('users', 'sales.user_id', '=', 'users.id')
            ->leftJoin('batches', 'sale_items.batch_id', '=', 'batches.id')
            ->where('sales.status', 'completed')
            ->whereBetween('sales.sale_date', [$this->startDate, $this->endDate])
            ->select([
                'sales.invoice_number',
                'sales.sale_date',
                'customers.name as customer_name',
                'users.name as user_name',
                'products.name as product_name',
                'products.cost_price',
                'products.selling_price',
                'sale_items.quantity',
                'sale_items.unit_price',
                'sale_items.total_price',
                'batches.batch_number'
            ])
            ->orderBy('sales.sale_date', 'desc');
    }

    /**
     * @return int
     */
    public function chunkSize(): int
    {
        return 1000;
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Invoice Number',
            'Date',
            'Customer',
            'Product Name',
            'Quantity',
            'Selling Price',
            'Cost Price',
            'Net Price',
            'Revenue',
            'Cost Profit/Unit',
            'Net Profit/Unit',
            'Total Cost Profit',
            'Total Net Profit',
            'Cost Margin %',
            'Net Margin %'
        ];
    }

    /**
     * @param mixed $row
     * @return array
     */
    public function map($row): array
    {
        // Safely format values with null checks
        $invoiceNumber = $row->invoice_number ?? 'N/A';
        $saleDate = $row->sale_date ? date('Y-m-d', strtotime($row->sale_date)) : 'N/A';
        $customerName = $row->customer_name ? trim($row->customer_name) : 'Walk-in Customer';
        $productName = $row->product_name ? trim($row->product_name) : 'N/A';

        // Clean and format numeric values
        $quantity = (int) ($row->quantity ?? 0);
        $unitPrice = (float) ($row->unit_price ?? 0);
        $costPrice = (float) ($row->cost_price ?? 0);
        $totalPrice = (float) ($row->total_price ?? 0);

        // Calculate net price (simplified - using cost price as fallback)
        $netPrice = $costPrice; // We'll calculate this more accurately later if needed

        // Calculate profits based on different pricing tiers
        $costProfit = $unitPrice - $costPrice;
        $netProfit = $unitPrice - $netPrice;
        $costMargin = $costPrice > 0 ? (($unitPrice - $costPrice) / $costPrice) * 100 : 0;
        $netMargin = $netPrice > 0 ? (($unitPrice - $netPrice) / $netPrice) * 100 : 0;

        return [
            $invoiceNumber,
            $saleDate,
            $customerName,
            $productName,
            $quantity,
            number_format($unitPrice, 2),
            number_format($costPrice, 2),
            number_format($netPrice, 2),
            number_format($totalPrice, 2),
            number_format($costProfit, 2),
            number_format($netProfit, 2),
            number_format($costProfit * $quantity, 2),
            number_format($netProfit * $quantity, 2),
            number_format($costMargin, 1),
            number_format($netMargin, 1),
        ];
    }

    /**
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as header
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '8B5CF6'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ],
                ],
            ],
            // Style all data rows
            'A2:O10000' => [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'CCCCCC'],
                    ],
                ],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
            // Style price columns (F, G, H, I) as currency
            'F:I' => [
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_RIGHT,
                ],
                'numberFormat' => [
                    'formatCode' => '#,##0.00',
                ],
            ],
            // Style profit columns (J, K, L, M) as currency
            'J:M' => [
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_RIGHT,
                ],
                'numberFormat' => [
                    'formatCode' => '#,##0.00',
                ],
            ],
            // Style margin columns (N, O) as percentage
            'N:O' => [
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_RIGHT,
                ],
                'numberFormat' => [
                    'formatCode' => '0.0%',
                ],
            ],
            // Style quantity column (E)
            'E:E' => [
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
            ],
            // Style date column (B)
            'B:B' => [
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
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
            'A' => 15, // Invoice Number
            'B' => 12, // Date
            'C' => 20, // Customer
            'D' => 25, // Product Name
            'E' => 8,  // Quantity
            'F' => 12, // Selling Price
            'G' => 12, // Cost Price
            'H' => 12, // Net Price
            'I' => 12, // Revenue
            'J' => 12, // Cost Profit/Unit
            'K' => 12, // Net Profit/Unit
            'L' => 12, // Total Cost Profit
            'M' => 12, // Total Net Profit
            'N' => 12, // Cost Margin %
            'O' => 12, // Net Margin %
        ];
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'Profit Analysis';
    }
}
