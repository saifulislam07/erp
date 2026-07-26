<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Product Report</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #333; padding: 4px 6px; text-align: left; }
        th { background-color: #eee; }
    </style>
</head>
<body>
    <h2>Product Report</h2>
    <p>
        From: {{ $fromDate ?? 'N/A' }} To: {{ $toDate ?? 'N/A' }}
        @if ($category) | Category: {{ $category->name }} @endif
    </p>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Category</th>
                <th>Unit</th>
                <th>MRP</th>
                <th>Purchase</th>
                <th>Sale</th>
                <th>VAT %</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($products as $product)
                <tr>
                    <td>{{ $product->unique_id }}</td>
                    <td>{{ $product->name }}</td>
                    <td>{{ $product->category?->name }}</td>
                    <td>{{ $product->unit?->symbol }}</td>
                    <td>{{ $product->mrp_price }}</td>
                    <td>{{ $product->purchase_price }}</td>
                    <td>{{ $product->sale_price }}</td>
                    <td>{{ $product->vat_percentage }}</td>
                    <td>{{ $product->status ? 'Active' : 'Inactive' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
