<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class StockExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(private readonly Collection $stocks)
    {
    }

    public function collection(): Collection
    {
        return $this->stocks;
    }

    public function headings(): array
    {
        return ['Product', 'Category', 'Store', 'Batch', 'Quantity', 'Purchase Price', 'Value', 'Expiry Date'];
    }

    public function map($stock): array
    {
        return [
            $stock->product->name,
            $stock->product->category?->name,
            $stock->store->name,
            $stock->batch_number,
            $stock->quantity,
            $stock->purchase_price,
            $stock->quantity * $stock->purchase_price,
            $stock->expiry_date?->format('Y-m-d'),
        ];
    }
}
