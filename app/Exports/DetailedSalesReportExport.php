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

class DetailedSalesReportExport implements FromQuery, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithTitle, ShouldAutoSize, WithChunkReading
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
                'products.code as product_code',
                'batches.batch_number',
                'sale_items.quantity',
                'sale_items.unit_price',
                'sale_items.total_price',
                'sales.payment_method',
                'sales.status'
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
            'Staff Member',
            'Product Name',
            'Product Code',
            'Batch Number',
            'Quantity Sold',
            'Unit Price ($)',
            'Total Price ($)',
            'Payment Method',
            'Sale Status'
        ];
    }

    /**
     * @param mixed $row
     * @return array
     */
    public function map($row): array
    {
        // Safely get values with null checks and trimming
        $invoiceNumber = $row->invoice_number ?? 'N/A';
        $saleDate = $row->sale_date ? date('Y-m-d', strtotime($row->sale_date)) : 'N/A';
        $customerName = $row->customer_name ? trim($row->customer_name) : 'Walk-in Customer';
        $staffName = $row->user_name ? trim($row->user_name) : 'N/A';
        $productName = $row->product_name ? trim($row->product_name) : 'N/A';
        $productCode = $row->product_code ? trim($row->product_code) : 'N/A';
        $batchNumber = $row->batch_number ? trim($row->batch_number) : 'N/A';

        return [
            $invoiceNumber,
            $saleDate,
            $customerName,
            $staffName,
            $productName,
            $productCode,
            $batchNumber,
            (int) ($row->quantity ?? 0),
            number_format((float) ($row->unit_price ?? 0), 2),
            number_format((float) ($row->total_price ?? 0), 2),
            ucfirst(trim($row->payment_method ?? 'Cash')),
            ucfirst(trim($row->status ?? 'Unknown'))
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
                    'startColor' => ['rgb' => '2E7D32'],
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
            'A2:L10000' => [
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
            // Style price columns (I, J) as currency
            'I:J' => [
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_RIGHT,
                ],
                'numberFormat' => [
                    'formatCode' => '#,##0.00',
                ],
            ],
            // Style quantity column (H)
            'H:H' => [
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
            'D' => 15, // Staff Member
            'E' => 25, // Product Name
            'F' => 12, // Product Code
            'G' => 15, // Batch Number
            'H' => 10, // Quantity
            'I' => 12, // Unit Price
            'J' => 12, // Total Price
            'K' => 12, // Payment Method
            'L' => 10, // Status
        ];
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'Detailed Sales Report';
    }
}
