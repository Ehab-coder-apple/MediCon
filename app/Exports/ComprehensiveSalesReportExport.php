<?php

namespace App\Exports;

use App\Models\Sale;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithTitle;

class ComprehensiveSalesReportExport implements WithMultipleSheets
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
    public function sheets(): array
    {
        return [
            'Summary' => new SalesReportExport($this->startDate, $this->endDate),
            'Detailed' => new DetailedSalesReportExport($this->startDate, $this->endDate),
            'Profit Analysis' => new \App\Exports\ProfitAnalysisReportExport($this->startDate, $this->endDate),
            'Statistics' => new SalesStatisticsExport($this->startDate, $this->endDate),
        ];
    }
}
