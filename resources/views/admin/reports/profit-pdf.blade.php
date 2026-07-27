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
        <tr><th>Gross Revenue</th><td>{{ money($summary['gross_revenue'], false) }}</td></tr>
        <tr><th>Cost of Goods</th><td>{{ money($summary['cost_of_goods'], false) }}</td></tr>
        <tr><th>Gross Profit</th><td>{{ money($summary['gross_profit'], false) }}</td></tr>
        <tr><th>Total Expenses</th><td>{{ money($summary['total_expenses'], false) }}</td></tr>
        <tr><th>VAT Collected</th><td>{{ money($summary['vat_collected'], false) }}</td></tr>
        <tr><th>Net Profit</th><td><strong>{{ money($summary['net_profit'], false) }}</strong></td></tr>
    </table>
</body>
</html>
