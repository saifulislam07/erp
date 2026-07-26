@extends('layouts.admin')

@section('content_title', 'Cash & Bank')

@section('content_body')
    <div class="row">
        <div class="col-md-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ number_format($cashBalance, 2) }}</h3>
                    <p>Cash Balance</p>
                </div>
                <div class="icon"><i class="fas fa-money-bill-wave"></i></div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ number_format($bankBalance, 2) }}</h3>
                    <p>Bank Balance (incl. Mobile Banking)</p>
                </div>
                <div class="icon"><i class="fas fa-university"></i></div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Recent Transactions (last 30)</h3>
            <div class="card-tools">
                <a href="{{ route('admin.cash-bank.transactions') }}" class="btn btn-secondary btn-sm">All Transactions</a>
                <a href="{{ route('admin.cash-bank.transfer.form') }}" class="btn btn-primary btn-sm">Transfer</a>
                <a href="{{ route('admin.cash-bank.transfer-history') }}" class="btn btn-secondary btn-sm">Transfer History</a>
            </div>
        </div>
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Method</th>
                        <th>Amount</th>
                        <th>Reference</th>
                        <th>Description</th>
                        <th>Cash Balance</th>
                        <th>Bank Balance</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($recentTransactions as $tx)
                        <tr>
                            <td>{{ $tx->transaction_date->format('Y-m-d') }}</td>
                            <td>
                                <span class="badge badge-{{ $tx->transaction_type === 'credit' ? 'success' : 'danger' }}">
                                    {{ ucfirst($tx->transaction_type) }}
                                </span>
                            </td>
                            <td>{{ ucfirst(str_replace('_', ' ', $tx->method)) }}</td>
                            <td>{{ $tx->amount }}</td>
                            <td>{{ class_basename($tx->reference_type) }} #{{ $tx->reference_id }}</td>
                            <td>{{ $tx->description }}</td>
                            <td>{{ $tx->balance_cash_after }}</td>
                            <td>{{ $tx->balance_bank_after }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
