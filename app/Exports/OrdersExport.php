<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class OrdersExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(private readonly Collection $orders)
    {
    }

    public function collection(): Collection
    {
        return $this->orders;
    }

    public function headings(): array
    {
        return ['Order ID', 'Client', 'Status', 'Total', 'Payment Method', 'Date'];
    }

    public function map($order): array
    {
        return [
            $order->order_id,
            $order->client?->name,
            ucfirst(str_replace('_', ' ', $order->status)),
            $order->total_amount,
            ucfirst(str_replace('_', ' ', $order->payment_method)),
            $order->created_at->format('Y-m-d'),
        ];
    }
}
