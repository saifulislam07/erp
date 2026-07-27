@extends('admin.invoices.layout', [
    'documentTitle' => 'Sales Invoice',
    'documentNumber' => $invoice->invoice_number,
    'accentColor' => '#4f46e5',
])

@php
    $customerName = $sale->customer?->name ?: ($sale->customer_name ?: 'Walk-in customer');
    $itemDiscount = $sale->items->sum(fn ($item) => (float) $item->discount_amount);
    $totalDiscount = (float) $sale->discount_amount + $itemDiscount;
@endphp

@section('stamp')
    <span class="stamp stamp-{{ $sale->payment_status }}">{{ $sale->payment_status }}</span>
@endsection

@section('meta')
    <table class="meta">
        <tr>
            <td>
                <span class="meta-label">Billed to</span>
                <span class="meta-value">
                    <strong>{{ $customerName }}</strong>
                    @if ($sale->customer?->business_name)
                        <br>{{ $sale->customer->business_name }}
                    @endif
                    @if ($sale->customer?->phone)
                        <br>{{ $sale->customer->phone }}
                    @endif
                    @if ($sale->customer?->address)
                        <br>{{ $sale->customer->address }}
                    @endif
                </span>
            </td>
            <td>
                <span class="meta-label">Invoice details</span>
                <span class="meta-value">
                    Sale ref. <strong>{{ $sale->sale_id }}</strong><br>
                    Date {{ $sale->sale_date->format('d M Y') }}<br>
                    Customer type {{ ucwords(str_replace('_', ' ', $sale->customer_type)) }}
                </span>
            </td>
            <td>
                <span class="meta-label">Payment</span>
                <span class="meta-value">
                    {{ ucwords(str_replace('_', ' ', $sale->payment_method)) }}<br>
                    @if ($sale->transaction_reference)
                        Ref. {{ $sale->transaction_reference }}<br>
                    @endif
                    Issued by {{ $sale->creator?->name ?? '—' }}
                </span>
            </td>
        </tr>
    </table>
@endsection

@section('items')
    <table class="items">
        <thead>
            <tr>
                <th style="width: 28px">#</th>
                <th>Item</th>
                <th class="num">Qty</th>
                <th class="num">Unit price</th>
                <th class="num">Discount</th>
                <th class="num">VAT</th>
                <th class="num">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($sale->items as $item)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>
                        {{ $item->product?->name ?? 'Deleted product' }}
                        @if ($item->product?->unique_id)
                            <span class="line-note">{{ $item->product->unique_id }}</span>
                        @endif
                    </td>
                    <td class="num">
                        {{ qty($item->quantity) }}
                        @if ($item->product?->unit)
                            <span class="line-note">{{ $item->product->unit->name }}</span>
                        @endif
                    </td>
                    <td class="num">{{ money($item->unit_price, false) }}</td>
                    <td class="num">{{ (float) $item->discount_amount > 0 ? money($item->discount_amount, false) : '—' }}</td>
                    <td class="num">
                        {{ (float) $item->vat_amount > 0 ? money($item->vat_amount, false) : '—' }}
                        @if ((float) $item->vat_percentage > 0)
                            <span class="line-note">{{ percent($item->vat_percentage) }}</span>
                        @endif
                    </td>
                    <td class="num">{{ money($item->total_price, false) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection

@section('summary')
    <table class="totals">
        <tr>
            <td style="width: 55%; padding-right: 18px;">
                <div class="in-words">
                    <strong>Amount in words:</strong><br>
                    {{ amount_in_words($sale->total_amount) }}
                </div>

                @if ($sale->note)
                    <div style="font-size: 10px; color: #64748b;">
                        <strong style="color: #0f172a;">Note:</strong> {{ $sale->note }}
                    </div>
                @endif
            </td>
            <td style="width: 45%;">
                <table class="summary">
                    <tr>
                        <td class="label">Subtotal</td>
                        <td class="value">{{ money($sale->subtotal, false) }}</td>
                    </tr>
                    @if ($totalDiscount > 0)
                        <tr>
                            <td class="label">Discount</td>
                            <td class="value">&minus; {{ money($totalDiscount, false) }}</td>
                        </tr>
                    @endif
                    @if ((float) $sale->vat_amount > 0)
                        <tr>
                            <td class="label">VAT</td>
                            <td class="value">{{ money($sale->vat_amount, false) }}</td>
                        </tr>
                    @endif
                    <tr class="grand">
                        <td class="label">Total</td>
                        <td class="value">{{ money($sale->total_amount, false) }}</td>
                    </tr>
                    <tr>
                        <td class="label">Paid</td>
                        <td class="value">{{ money($sale->paid_amount, false) }}</td>
                    </tr>
                    @if ((float) $sale->due_amount > 0)
                        <tr class="due">
                            <td class="label">Balance due</td>
                            <td class="value">{{ money($sale->due_amount, false) }}</td>
                        </tr>
                    @endif
                </table>
            </td>
        </tr>
    </table>
@endsection

@section('footnote')
    Goods once sold are taken back only under the return policy.
@endsection
