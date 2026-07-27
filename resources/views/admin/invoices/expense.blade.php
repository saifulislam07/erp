@extends('admin.invoices.layout', [
    'documentTitle' => 'Expense Voucher',
    'documentNumber' => $invoice->invoice_number,
    'accentColor' => '#b91c1c',
])

@section('meta')
    <table class="meta">
        <tr>
            <td>
                <span class="meta-label">Expense</span>
                <span class="meta-value">
                    <strong>{{ $expense->expenseHead?->name ?? '—' }}</strong><br>
                    Ref. {{ $expense->expense_id }}
                </span>
            </td>
            <td>
                <span class="meta-label">Date</span>
                <span class="meta-value">{{ $expense->expense_date->format('d M Y') }}</span>
            </td>
            <td>
                <span class="meta-label">Paid from</span>
                <span class="meta-value">{{ ucwords(str_replace('_', ' ', $expense->payment_method)) }}</span>
            </td>
        </tr>
    </table>
@endsection

@section('items')
    <table class="items">
        <thead>
            <tr>
                <th>Description</th>
                <th class="num" style="width: 150px">Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    {{ $expense->expenseHead?->name ?? 'Expense' }}
                    @if ($expense->description)
                        <span class="line-note">{{ $expense->description }}</span>
                    @endif
                </td>
                <td class="num">{{ money($expense->amount, false) }}</td>
            </tr>
        </tbody>
    </table>
@endsection

@section('summary')
    <table class="totals">
        <tr>
            <td style="width: 55%; padding-right: 18px;">
                <div class="in-words">
                    <strong>Amount in words:</strong><br>
                    {{ amount_in_words($expense->amount) }}
                </div>

                @if ($expense->receipt_file)
                    <div style="font-size: 10px; color: #64748b;">
                        A receipt is attached to this expense in the system.
                    </div>
                @endif
            </td>
            <td style="width: 45%;">
                <table class="summary">
                    <tr class="grand">
                        <td class="label">Total paid</td>
                        <td class="value">{{ money($expense->amount, false) }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
@endsection
