<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Expense Invoice {{ $expense->expense_id }}</title>
    <style>
        body { font-family: sans-serif; font-size: 13px; color: #222; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { margin: 0; font-size: 22px; }
        table.details { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table.details th, table.details td { border: 1px solid #333; padding: 6px 10px; text-align: left; }
        table.details th { background-color: #eee; width: 30%; }
    </style>
</head>
<body>
    @include('admin.invoices.partials.header', ['invoiceTitle' => 'Expense Invoice'])

    <table class="details">
        <tr><th>Invoice #</th><td>{{ $invoice->invoice_number }}</td></tr>
        <tr><th>Expense ID</th><td>{{ $expense->expense_id }}</td></tr>
        <tr><th>Expense Head</th><td>{{ $expense->expenseHead->name }}</td></tr>
        <tr><th>Amount</th><td>{{ number_format($expense->amount, 2) }}</td></tr>
        <tr><th>Date</th><td>{{ $expense->expense_date->format('Y-m-d') }}</td></tr>
        <tr><th>Payment Method</th><td>{{ ucfirst(str_replace('_', ' ', $expense->payment_method)) }}</td></tr>
        <tr><th>Description</th><td>{{ $expense->description ?? '-' }}</td></tr>
        @if ($expense->receipt_file)
            <tr><th>Receipt</th><td>{{ $expense->receipt_file }}</td></tr>
        @endif
    </table>
</body>
</html>
