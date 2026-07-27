@extends('admin.invoices.layout', [
    'documentTitle' => 'Purchase Invoice',
    'documentNumber' => $invoice->invoice_number,
    'accentColor' => '#0f766e',
])

@php
    $returned = (float) $purchase->returns->sum('total_amount');
@endphp

@section('stamp')
    <span class="stamp stamp-{{ $purchase->payment_status }}">{{ $purchase->payment_status }}</span>
@endsection

@section('meta')
    <table class="meta">
        <tr>
            <td>
                <span class="meta-label">Supplier</span>
                <span class="meta-value">
                    <strong>{{ $purchase->supplier?->name ?? '—' }}</strong>
                    @if ($purchase->supplier?->company_name)
                        <br>{{ $purchase->supplier->company_name }}
                    @endif
                    @if ($purchase->supplier?->phone)
                        <br>{{ $purchase->supplier->phone }}
                    @endif
                    @if ($purchase->supplier?->address)
                        <br>{{ $purchase->supplier->address }}
                    @endif
                </span>
            </td>
            <td>
                <span class="meta-label">Purchase details</span>
                <span class="meta-value">
                    Purchase ref. <strong>{{ $purchase->purchase_id }}</strong><br>
                    Date {{ $purchase->purchase_date->format('d M Y') }}<br>
                    @if ($purchase->invoice_number)
                        Supplier invoice {{ $purchase->invoice_number }}
                    @endif
                </span>
            </td>
            <td>
                <span class="meta-label">Payment</span>
                <span class="meta-value">
                    {{ ucwords(str_replace('_', ' ', $purchase->payment_method ?? '—')) }}<br>
                    Recorded by {{ $purchase->creator?->name ?? '—' }}
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
                <th class="num">Unit cost</th>
                <th class="num">VAT</th>
                <th class="num">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($purchase->items as $item)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>
                        {{ $item->product?->name ?? 'Deleted product' }}
                        @if ($item->expiry_date)
                            <span class="line-note">Expires {{ $item->expiry_date->format('d M Y') }}</span>
                        @endif
                    </td>
                    <td class="num">
                        {{ qty($item->quantity) }}
                        @if ($item->product?->unit)
                            <span class="line-note">{{ $item->product->unit->name }}</span>
                        @endif
                    </td>
                    <td class="num">{{ money($item->purchase_price, false) }}</td>
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
                    {{ amount_in_words($purchase->total_amount) }}
                </div>

                @if ($returned > 0)
                    <div style="font-size: 10px; color: #64748b; margin-bottom: 8px;">
                        <strong style="color: #0f172a;">Returned to supplier:</strong>
                        {{ money($returned, false) }} across
                        {{ $purchase->returns->count() }} {{ Str::plural('return', $purchase->returns->count()) }}.
                    </div>
                @endif

                @if ($purchase->note)
                    <div style="font-size: 10px; color: #64748b;">
                        <strong style="color: #0f172a;">Note:</strong> {{ $purchase->note }}
                    </div>
                @endif
            </td>
            <td style="width: 45%;">
                <table class="summary">
                    <tr>
                        <td class="label">Subtotal</td>
                        <td class="value">{{ money($purchase->subtotal, false) }}</td>
                    </tr>
                    @if ((float) $purchase->vat_amount > 0)
                        <tr>
                            <td class="label">VAT</td>
                            <td class="value">{{ money($purchase->vat_amount, false) }}</td>
                        </tr>
                    @endif
                    <tr class="grand">
                        <td class="label">Total</td>
                        <td class="value">{{ money($purchase->total_amount, false) }}</td>
                    </tr>
                    <tr>
                        <td class="label">Paid</td>
                        <td class="value">{{ money($purchase->paid_amount, false) }}</td>
                    </tr>
                    @if ($returned > 0)
                        <tr>
                            <td class="label">Returned</td>
                            <td class="value">&minus; {{ money($returned, false) }}</td>
                        </tr>
                    @endif
                    @if ((float) $purchase->due_amount > 0)
                        <tr class="due">
                            <td class="label">Balance payable</td>
                            <td class="value">{{ money(max(0, (float) $purchase->due_amount - $returned), false) }}</td>
                        </tr>
                    @endif
                </table>
            </td>
        </tr>
    </table>
@endsection

@section('footnote')
    Please quote the purchase reference on all correspondence.
@endsection
