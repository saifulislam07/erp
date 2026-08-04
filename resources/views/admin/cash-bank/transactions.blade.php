@extends('layouts.admin')

@section('content_title', 'Cash & Bank Transactions')

@section('content_body')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Filters</h3>
        </div>
        <form action="{{ route('admin.cash-bank.transactions') }}" method="get" id="tx-filter" data-no-submit-guard>
            <div class="card-body row">
                <div class="col-md-2">
                    <label>Method</label>
                    <select name="method" class="form-control">
                        <option value="">-- All --</option>
                        <option value="cash" {{ request('method') === 'cash' ? 'selected' : '' }}>Cash</option>
                        <option value="bank" {{ request('method') === 'bank' ? 'selected' : '' }}>Bank</option>
                        <option value="mobile_banking" {{ request('method') === 'mobile_banking' ? 'selected' : '' }}>Mobile Banking</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label>Type</label>
                    <select name="type" class="form-control">
                        <option value="">-- All --</option>
                        <option value="credit" {{ request('type') === 'credit' ? 'selected' : '' }}>Credit</option>
                        <option value="debit" {{ request('type') === 'debit' ? 'selected' : '' }}>Debit</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label>Reference Type</label>
                    <input type="text" name="reference_type" class="form-control" placeholder="e.g. Purchase" value="{{ request('reference_type') }}">
                </div>
                <div class="col-md-2">
                    <label>From Date</label>
                    <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
                </div>
                <div class="col-md-2">
                    <label>To Date</label>
                    <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="#" class="btn btn-secondary ml-1" data-table-clear>Clear</a>
                </div>
            </div>
        </form>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title" id="tx-count">All Transactions</h3>
            <div class="card-tools">
                <a href="{{ route('admin.cash-bank.index') }}" class="btn btn-secondary btn-sm">Back</a>
            </div>
        </div>
        <div class="card-body">
            <table id="tx-table" class="table table-bordered table-striped" style="width: 100%">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Method</th>
                        <th>Amount</th>
                        <th>Reference</th>
                        <th>Description</th>
                        <th>By</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
@endsection

@push('js')
    <script>
        $(function () {
            ERP.serverTable('#tx-table', {
                url: '{{ route('admin.cash-bank.transactions') }}',
                filter: '#tx-filter',
                count: '#tx-count',
                noun: 'transaction',
                empty: 'No transactions recorded yet.',
                order: [[0, 'desc']],
                columns: [
                    { data: 'dated_on', name: 'dated_on', searchable: false },
                    { data: 'type_badge', name: 'type_badge' },
                    { data: 'method', name: 'method' },
                    { data: 'amount', name: 'amount' },
                    { data: 'reference', name: 'reference' },
                    { data: 'description', name: 'description' },
                    { data: 'created_by_name', name: 'created_by_name' },
                ],
            });
        });
    </script>
@endpush
