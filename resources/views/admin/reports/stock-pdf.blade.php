<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Stock Report</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #333; padding: 4px 6px; text-align: left; }
        th { background-color: #eee; }
    </style>
</head>
<body>
    <h2>Stock Report</h2>

    <table>
        <thead>
            <tr>
                <th>Product</th>
                <th>Category</th>
                <th>Store</th>
                <th>Batch</th>
                <th>Quantity</th>
                <th>Purchase Price</th>
                <th>Value</th>
                <th>Expiry Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($stocks as $stock)
                <tr>
                    <td>{{ $stock->product->name }}</td>
                    <td>{{ $stock->product->category?->name }}</td>
                    <td>{{ $stock->store->name }}</td>
                    <td>{{ $stock->batch_number }}</td>
                    <td>{{ $stock->quantity }}</td>
                    <td>{{ number_format($stock->purchase_price, 2) }}</td>
                    <td>{{ number_format($stock->quantity * $stock->purchase_price, 2) }}</td>
                    <td>{{ $stock->expiry_date?->format('Y-m-d') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="6">Total Value</th>
                <th colspan="2">{{ number_format($totalValue, 2) }}</th>
            </tr>
        </tfoot>
    </table>
</body>
</html>
