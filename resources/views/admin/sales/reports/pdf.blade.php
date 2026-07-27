<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Sale Report</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #333; padding: 4px 6px; text-align: left; }
        th { background-color: #eee; }
    </style>
</head>
<body>
    <h2>Sale Report</h2>
    <p>From: {{ $fromDate ?? 'N/A' }} To: {{ $toDate ?? 'N/A' }}</p>

    <table>
        <thead>
            <tr>
                <th>Sale ID</th>
                <th>Customer</th>
                <th>Date</th>
                <th>Subtotal</th>
                <th>Discount</th>
                <th>VAT</th>
                <th>Total</th>
                <th>Paid</th>
                <th>Due</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($sales as $sale)
                <tr>
                    <td>{{ $sale->sale_id }}</td>
                    <td>{{ $sale->customer_type === 'local' ? $sale->customer_name : $sale->customer?->name }}</td>
                    <td>{{ $sale->sale_date->format('Y-m-d') }}</td>
                    <td>{{ $sale->subtotal }}</td>
                    <td>{{ $sale->discount_amount }}</td>
                    <td>{{ $sale->vat_amount }}</td>
                    <td>{{ $sale->total_amount }}</td>
                    <td>{{ $sale->paid_amount }}</td>
                    <td>{{ $sale->due_amount }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="3">Totals</th>
                <th>{{ money($totals['subtotal'], false) }}</th>
                <th>{{ money($totals['discount_amount'], false) }}</th>
                <th>{{ money($totals['vat_amount'], false) }}</th>
                <th>{{ money($totals['total_amount'], false) }}</th>
                <th>{{ money($totals['paid_amount'], false) }}</th>
                <th>{{ money($totals['due_amount'], false) }}</th>
            </tr>
        </tfoot>
    </table>
</body>
</html>
