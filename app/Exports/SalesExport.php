<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SalesExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(private readonly Collection $sales)
    {
    }

    public function collection(): Collection
    {
        return $this->sales;
    }

    public function headings(): array
    {
        return ['Sale ID', 'Customer', 'Date', 'Subtotal', 'Discount', 'VAT', 'Total', 'Paid', 'Due', 'Status'];
    }

    public function map($sale): array
    {
        return [
            $sale->sale_id,
            $sale->customer_type === 'local' ? $sale->customer_name : $sale->customer?->name,
            $sale->sale_date->format('Y-m-d'),
            $sale->subtotal,
            $sale->discount_amount,
            $sale->vat_amount,
            $sale->total_amount,
            $sale->paid_amount,
            $sale->due_amount,
            ucfirst($sale->payment_status),
        ];
    }
}
