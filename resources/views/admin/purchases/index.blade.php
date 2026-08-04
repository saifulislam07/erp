@extends('layouts.admin')

@section('content_title', 'Purchases')

@section('content_body')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Filters</h3>
        </div>
        <form action="{{ route('admin.purchases.index') }}" method="get" id="purchases-filter" data-no-submit-guard>
            <div class="card-body row">
                <div class="col-md-2">
                    <label>Purchase ID</label>
                    <input type="text" name="purchase_id" class="form-control" value="{{ request('purchase_id') }}">
                </div>
                <div class="col-md-2">
                    <label>Supplier</label>
                    <select name="supplier_id" class="form-control">
                        <option value="">-- All --</option>
                        @foreach ($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" {{ (int) request('supplier_id') === $supplier->id ? 'selected' : '' }}>
                                {{ $supplier->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label>From Date</label>
                    <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
                </div>
                <div class="col-md-2">
                    <label>To Date</label>
                    <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
                </div>
                <div class="col-md-3">
                    <label>Period</label>
                    <div>
                        <div class="form-check form-check-inline">
                            <input type="radio" name="period" value="weekly" class="form-check-input" {{ request('period') === 'weekly' ? 'checked' : '' }}>
                            <label class="form-check-label">Weekly</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="radio" name="period" value="monthly" class="form-check-input" {{ request('period') === 'monthly' ? 'checked' : '' }}>
                            <label class="form-check-label">Monthly</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="radio" name="period" value="yearly" class="form-check-input" {{ request('period') === 'yearly' ? 'checked' : '' }}>
                            <label class="form-check-label">Yearly</label>
                        </div>
                    </div>
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="#" class="btn btn-secondary ml-1" data-table-clear>Clear</a>
                </div>
            </div>
        </form>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title" id="purchases-count">All Purchases</h3>
            <div class="card-tools">
                <a href="{{ route('admin.purchases.report') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-chart-bar"></i> Report
                </a>
                <a href="{{ route('admin.purchases.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> New Purchase
                </a>
            </div>
        </div>

        <div class="card-body">
            <table id="purchases-table" class="table table-bordered table-striped" style="width: 100%">
                <thead>
                    <tr>
                        <th>Purchase ID</th>
                        <th>Supplier</th>
                        <th>Date</th>
                        <th>Total</th>
                        <th>Paid</th>
                        <th>Due</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
@endsection

@push('js')
    <script>
        $(function () {
            ERP.serverTable('#purchases-table', {
                url: '{{ route('admin.purchases.index') }}',
                filter: '#purchases-filter',
                count: '#purchases-count',
                noun: 'purchase',
                empty: 'No purchases recorded yet.',
                order: [[2, 'desc']],
                columns: [
                    { data: 'purchase_id', name: 'purchase_id' },
                    { data: 'supplier_name', name: 'supplier_name' },
                    { data: 'purchase_date', name: 'purchase_date' },
                    { data: 'total_amount', name: 'total_amount' },
                    { data: 'paid_amount', name: 'paid_amount' },
                    { data: 'due_amount', name: 'due_amount' },
                    { data: 'state', name: 'state' },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-nowrap' },
                ],
            });
        });
    </script>
@endpush
