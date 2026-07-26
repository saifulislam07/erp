<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Profit Report</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #333; padding: 4px 6px; text-align: left; }
        th { background-color: #eee; }
    </style>
</head>
<body>
    <h2>Profit Report</h2>
    <p>From: {{ $fromDate ?? 'N/A' }} To: {{ $toDate ?? 'N/A' }}</p>

    <table>
        <tr><th>Gross Revenue</th><td>{{ number_format($summary['gross_revenue'], 2) }}</td></tr>
        <tr><th>Cost of Goods</th><td>{{ number_format($summary['cost_of_goods'], 2) }}</td></tr>
        <tr><th>Gross Profit</th><td>{{ number_format($summary['gross_profit'], 2) }}</td></tr>
        <tr><th>Total Expenses</th><td>{{ number_format($summary['total_expenses'], 2) }}</td></tr>
        <tr><th>VAT Collected</th><td>{{ number_format($summary['vat_collected'], 2) }}</td></tr>
        <tr><th>Net Profit</th><td><strong>{{ number_format($summary['net_profit'], 2) }}</strong></td></tr>
    </table>
</body>
</html>
