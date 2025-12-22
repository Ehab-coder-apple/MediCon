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

class SalesReportExport implements FromQuery, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithTitle, ShouldAutoSize, WithChunkReading
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
        return Sale::query()
            ->leftJoin('customers', 'sales.customer_id', '=', 'customers.id')
            ->leftJoin('users', 'sales.user_id', '=', 'users.id')
            ->where('sales.status', 'completed')
            ->whereBetween('sales.sale_date', [$this->startDate, $this->endDate])
            ->select([
                'sales.invoice_number',
                'sales.sale_date',
                'customers.name as customer_name',
                'users.name as user_name',
                'sales.total_price',
                'sales.payment_method',
                'sales.status',
                'sales.notes',
                \DB::raw('(SELECT COUNT(*) FROM sale_items WHERE sale_items.sale_id = sales.id) as items_count')
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
            'Items Count',
            'Total Amount ($)',
            'Payment Method',
            'Status',
            'Notes'
        ];
    }

    /**
     * @param mixed $sale
     * @return array
     */
    public function map($row): array
    {
        // Safely get values with null checks and trimming
        $invoiceNumber = $row->invoice_number ?? 'N/A';
        $saleDate = $row->sale_date ? date('Y-m-d', strtotime($row->sale_date)) : 'N/A';
        $customerName = $row->customer_name ? trim($row->customer_name) : 'Walk-in Customer';
        $staffName = $row->user_name ? trim($row->user_name) : 'N/A';
        $itemsCount = (int) ($row->items_count ?? 0);
        $totalPrice = (float) ($row->total_price ?? 0);
        $paymentMethod = ucfirst(trim($row->payment_method ?? 'Cash'));
        $status = ucfirst(trim($row->status ?? 'Unknown'));
        $notes = trim($row->notes ?? '');

        return [
            $invoiceNumber,
            $saleDate,
            $customerName,
            $staffName,
            $itemsCount,
            number_format($totalPrice, 2),
            $paymentMethod,
            $status,
            $notes
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
                    'startColor' => ['rgb' => '4472C4'],
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
            'A2:I1000' => [
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
            // Style amount column (F) as currency
            'F:F' => [
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_RIGHT,
                ],
                'numberFormat' => [
                    'formatCode' => '#,##0.00',
                ],
            ],
            // Style date column (B)
            'B:B' => [
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
            ],
            // Style items count column (E)
            'E:E' => [
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
            'E' => 10, // Items Count
            'F' => 15, // Total Amount
            'G' => 12, // Payment Method
            'H' => 10, // Status
            'I' => 25, // Notes
        ];
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'Sales Report';
    }
}
