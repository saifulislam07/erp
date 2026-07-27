<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Order Report</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #333; padding: 4px 6px; text-align: left; }
        th { background-color: #eee; }
    </style>
</head>
<body>
    <h2>Order Report</h2>
    <p>From: {{ $fromDate ?? 'N/A' }} To: {{ $toDate ?? 'N/A' }}</p>

    <table>
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Client</th>
                <th>Status</th>
                <th>Total</th>
                <th>Payment Method</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($orders as $order)
                <tr>
                    <td>{{ $order->order_id }}</td>
                    <td>{{ $order->client?->name }}</td>
                    <td>{{ ucfirst(str_replace('_', ' ', $order->status)) }}</td>
                    <td>{{ money($order->total_amount, false) }}</td>
                    <td>{{ ucfirst(str_replace('_', ' ', $order->payment_method)) }}</td>
                    <td>{{ $order->created_at->format('Y-m-d') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
