<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class PurchasesExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(private readonly Collection $purchases)
    {
    }

    public function collection(): Collection
    {
        return $this->purchases;
    }

    public function headings(): array
    {
        return ['Purchase ID', 'Supplier', 'Date', 'Subtotal', 'VAT', 'Total', 'Paid', 'Due', 'Status'];
    }

    public function map($purchase): array
    {
        return [
            $purchase->purchase_id,
            $purchase->supplier?->name,
            $purchase->purchase_date->format('Y-m-d'),
            $purchase->subtotal,
            $purchase->vat_amount,
            $purchase->total_amount,
            $purchase->paid_amount,
            $purchase->due_amount,
            ucfirst($purchase->payment_status),
        ];
    }
}
