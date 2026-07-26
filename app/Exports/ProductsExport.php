<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ProductsExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(private readonly Collection $products)
    {
    }

    public function collection(): Collection
    {
        return $this->products;
    }

    public function headings(): array
    {
        return ['ID', 'Name', 'Category', 'Unit', 'MRP Price', 'Purchase Price', 'Sale Price', 'VAT %', 'Status'];
    }

    public function map($product): array
    {
        return [
            $product->unique_id,
            $product->name,
            $product->category?->name,
            $product->unit?->symbol,
            $product->mrp_price,
            $product->purchase_price,
            $product->sale_price,
            $product->vat_percentage,
            $product->status ? 'Active' : 'Inactive',
        ];
    }
}
