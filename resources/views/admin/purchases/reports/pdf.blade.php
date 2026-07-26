<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Purchase Report</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #333; padding: 4px 6px; text-align: left; }
        th { background-color: #eee; }
    </style>
</head>
<body>
    <h2>Purchase Report</h2>
    <p>From: {{ $fromDate ?? 'N/A' }} To: {{ $toDate ?? 'N/A' }}</p>

    <table>
        <thead>
            <tr>
                <th>Purchase ID</th>
                <th>Supplier</th>
                <th>Date</th>
                <th>Subtotal</th>
                <th>VAT</th>
                <th>Total</th>
                <th>Paid</th>
                <th>Due</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($purchases as $purchase)
                <tr>
                    <td>{{ $purchase->purchase_id }}</td>
                    <td>{{ $purchase->supplier?->name }}</td>
                    <td>{{ $purchase->purchase_date->format('Y-m-d') }}</td>
                    <td>{{ $purchase->subtotal }}</td>
                    <td>{{ $purchase->vat_amount }}</td>
                    <td>{{ $purchase->total_amount }}</td>
                    <td>{{ $purchase->paid_amount }}</td>
                    <td>{{ $purchase->due_amount }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="3">Totals</th>
                <th>{{ number_format($totals['subtotal'], 2) }}</th>
                <th>{{ number_format($totals['vat_amount'], 2) }}</th>
                <th>{{ number_format($totals['total_amount'], 2) }}</th>
                <th>{{ number_format($totals['paid_amount'], 2) }}</th>
                <th>{{ number_format($totals['due_amount'], 2) }}</th>
            </tr>
        </tfoot>
    </table>
</body>
</html>
