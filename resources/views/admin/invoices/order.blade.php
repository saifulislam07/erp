@extends('admin.invoices.layout', [
    'documentTitle' => 'Order Invoice',
    'documentNumber' => $invoice->invoice_number,
    'accentColor' => '#b45309',
])

@section('stamp')
    <span class="stamp stamp-{{ $order->status === 'delivered' ? 'paid' : 'partial' }}">
        {{ str_replace('_', ' ', $order->status) }}
    </span>
@endsection

@section('meta')
    <table class="meta">
        <tr>
            <td>
                <span class="meta-label">Deliver to</span>
                <span class="meta-value">
                    <strong>{{ $order->client?->name ?? '—' }}</strong>
                    @if ($order->client?->business_name)
                        <br>{{ $order->client->business_name }}
                    @endif
                    @if ($order->client?->phone)
                        <br>{{ $order->client->phone }}
                    @endif
                    @if ($order->delivery_address ?? $order->client?->address)
                        <br>{{ $order->delivery_address ?? $order->client->address }}
                    @endif
                </span>
            </td>
            <td>
                <span class="meta-label">Order details</span>
                <span class="meta-value">
                    Order ref. <strong>{{ $order->order_id }}</strong><br>
                    Placed {{ $order->created_at->format('d M Y') }}<br>
                    Status {{ ucwords(str_replace('_', ' ', $order->status)) }}
                </span>
            </td>
            <td>
                <span class="meta-label">Payment</span>
                <span class="meta-value">
                    {{ ucwords(str_replace('_', ' ', $order->payment_method)) }}
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
            @foreach ($order->items as $item)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $item->product?->name ?? 'Deleted product' }}</td>
                    <td class="num">{{ qty($item->quantity) }}</td>
                    <td class="num">{{ money($item->unit_price, false) }}</td>
                    <td class="num">{{ (float) $item->discount_amount > 0 ? money($item->discount_amount, false) : '—' }}</td>
                    <td class="num">{{ (float) $item->vat_amount > 0 ? money($item->vat_amount, false) : '—' }}</td>
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
                    {{ amount_in_words($order->total_amount) }}
                </div>
            </td>
            <td style="width: 45%;">
                <table class="summary">
                    <tr>
                        <td class="label">Subtotal</td>
                        <td class="value">{{ money($order->subtotal, false) }}</td>
                    </tr>
                    @if ((float) $order->discount_amount > 0)
                        <tr>
                            <td class="label">Discount</td>
                            <td class="value">&minus; {{ money($order->discount_amount, false) }}</td>
                        </tr>
                    @endif
                    @if ((float) $order->vat_amount > 0)
                        <tr>
                            <td class="label">VAT</td>
                            <td class="value">{{ money($order->vat_amount, false) }}</td>
                        </tr>
                    @endif
                    @if ((float) $order->delivery_charge > 0)
                        <tr>
                            <td class="label">Delivery</td>
                            <td class="value">{{ money($order->delivery_charge, false) }}</td>
                        </tr>
                    @endif
                    <tr class="grand">
                        <td class="label">Total</td>
                        <td class="value">{{ money($order->total_amount, false) }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
@endsection
