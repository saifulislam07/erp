<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Purchase Invoice {{ $purchase->purchase_id }}</title>
    <style>
        body { font-family: sans-serif; font-size: 13px; color: #222; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { margin: 0; font-size: 22px; }
        .meta { width: 100%; margin-bottom: 20px; }
        .meta td { vertical-align: top; padding: 2px 0; }
        table.items { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table.items th, table.items td { border: 1px solid #333; padding: 5px 8px; text-align: left; }
        table.items th { background-color: #eee; }
        table.summary { width: 40%; margin-left: auto; border-collapse: collapse; }
        table.summary td { padding: 4px 8px; }
        table.summary tr.grand-total td { font-weight: bold; border-top: 2px solid #333; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ config('app.name') }}</h1>
        <p>Purchase Invoice</p>
    </div>

    <table class="meta">
        <tr>
            <td>
                <strong>Invoice #:</strong> {{ $invoice->invoice_number }}<br>
                <strong>Purchase ID:</strong> {{ $purchase->purchase_id }}<br>
                <strong>Date:</strong> {{ $purchase->purchase_date->format('Y-m-d') }}
            </td>
            <td style="text-align: right;">
                <strong>Supplier:</strong> {{ $purchase->supplier->name }}<br>
                <strong>Supplier Phone:</strong> {{ $purchase->supplier->phone }}<br>
                <strong>Payment Method:</strong> {{ ucfirst(str_replace('_', ' ', $purchase->payment_method)) }}
            </td>
        </tr>
    </table>

    <table class="items">
        <thead>
            <tr>
                <th>Product</th>
                <th>Qty</th>
                <th>Purchase Price</th>
                <th>VAT %</th>
                <th>VAT Amount</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($purchase->items as $item)
                <tr>
                    <td>{{ $item->product->name }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>{{ number_format($item->purchase_price, 2) }}</td>
                    <td>{{ number_format($item->vat_percentage, 2) }}</td>
                    <td>{{ number_format($item->vat_amount, 2) }}</td>
                    <td>{{ number_format($item->total_price, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="summary">
        <tr><td>Subtotal</td><td>{{ number_format($purchase->subtotal, 2) }}</td></tr>
        <tr><td>VAT</td><td>{{ number_format($purchase->vat_amount, 2) }}</td></tr>
        <tr class="grand-total"><td>Grand Total</td><td>{{ number_format($purchase->total_amount, 2) }}</td></tr>
        <tr><td>Paid</td><td>{{ number_format($purchase->paid_amount, 2) }}</td></tr>
        <tr><td>Due</td><td>{{ number_format($purchase->due_amount, 2) }}</td></tr>
    </table>
</body>
</html>
