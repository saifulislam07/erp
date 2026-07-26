<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AssetsExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(private readonly Collection $assets)
    {
    }

    public function collection(): Collection
    {
        return $this->assets;
    }

    public function headings(): array
    {
        return ['Asset ID', 'Name', 'Serial Number', 'Category', 'Purchase Price', 'Purchase Date', 'Quantity', 'Status'];
    }

    public function map($asset): array
    {
        return [
            $asset->asset_id,
            $asset->name,
            $asset->serial_number,
            $asset->category,
            $asset->purchase_price,
            $asset->purchase_date->format('Y-m-d'),
            $asset->quantity,
            ucfirst($asset->status),
        ];
    }
}
