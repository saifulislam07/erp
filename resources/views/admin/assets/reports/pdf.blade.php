<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Asset Report</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #333; padding: 4px 6px; text-align: left; }
        th { background-color: #eee; }
    </style>
</head>
<body>
    <h2>Asset Report</h2>
    <p>From: {{ $fromDate ?? 'N/A' }} To: {{ $toDate ?? 'N/A' }}</p>

    <table>
        <thead>
            <tr>
                <th>Asset ID</th>
                <th>Name</th>
                <th>Category</th>
                <th>Purchase Price</th>
                <th>Purchase Date</th>
                <th>Quantity</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($assets as $asset)
                <tr>
                    <td>{{ $asset->asset_id }}</td>
                    <td>{{ $asset->name }}</td>
                    <td>{{ $asset->category }}</td>
                    <td>{{ $asset->purchase_price }}</td>
                    <td>{{ $asset->purchase_date->format('Y-m-d') }}</td>
                    <td>{{ $asset->quantity }}</td>
                    <td>{{ ucfirst($asset->status) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="3">Total Value</th>
                <th>{{ number_format($assets->sum('purchase_price'), 2) }}</th>
                <th colspan="3"></th>
            </tr>
        </tfoot>
    </table>
</body>
</html>
