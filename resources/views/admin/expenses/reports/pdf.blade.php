<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Expense Report</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #333; padding: 4px 6px; text-align: left; }
        th { background-color: #eee; }
    </style>
</head>
<body>
    <h2>Expense Report</h2>
    <p>From: {{ $fromDate ?? 'N/A' }} To: {{ $toDate ?? 'N/A' }}</p>

    <h3>Breakdown by Head</h3>
    <table>
        @foreach ($byHead as $headName => $total)
            <tr><th>{{ $headName }}</th><td>{{ number_format($total, 2) }}</td></tr>
        @endforeach
        <tr><th>Grand Total</th><td>{{ number_format($grandTotal, 2) }}</td></tr>
    </table>

    <h3>Details</h3>
    <table>
        <thead>
            <tr><th>Expense ID</th><th>Head</th><th>Amount</th><th>Date</th><th>Method</th></tr>
        </thead>
        <tbody>
            @foreach ($expenses as $expense)
                <tr>
                    <td>{{ $expense->expense_id }}</td>
                    <td>{{ $expense->expenseHead->name }}</td>
                    <td>{{ $expense->amount }}</td>
                    <td>{{ $expense->expense_date->format('Y-m-d') }}</td>
                    <td>{{ ucfirst($expense->payment_method) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
