<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ProfitExport implements FromCollection, WithHeadings
{
    public function __construct(private readonly array $summary)
    {
    }

    public function collection(): Collection
    {
        return collect([
            ['Gross Revenue', $this->summary['gross_revenue']],
            ['Cost of Goods', $this->summary['cost_of_goods']],
            ['Gross Profit', $this->summary['gross_profit']],
            ['Total Expenses', $this->summary['total_expenses']],
            ['VAT Collected', $this->summary['vat_collected']],
            ['Net Profit', $this->summary['net_profit']],
        ]);
    }

    public function headings(): array
    {
        return ['Metric', 'Amount'];
    }
}
