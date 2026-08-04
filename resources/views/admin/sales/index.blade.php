@extends('layouts.admin')

@section('content_title', 'Sales')

@section('content_body')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Filters</h3>
        </div>
        <form action="{{ route('admin.sales.index') }}" method="get" id="sales-filter" data-no-submit-guard>
            <div class="card-body row">
                <div class="col-md-2">
                    <label>From Date</label>
                    <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
                </div>
                <div class="col-md-2">
                    <label>To Date</label>
                    <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
                </div>
                <div class="col-md-3">
                    <label>Product Name</label>
                    <input type="text" name="product_name" class="form-control" value="{{ request('product_name') }}">
                </div>
                <div class="col-md-3">
                    <label>Customer Type</label>
                    <select name="customer_type" class="form-control">
                        <option value="">-- All --</option>
                        <option value="local" {{ request('customer_type') === 'local' ? 'selected' : '' }}>Local</option>
                        <option value="client_agent" {{ request('customer_type') === 'client_agent' ? 'selected' : '' }}>Client/Agent</option>
                    </select>
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
            <h3 class="card-title" id="sales-count">All Sales</h3>
            <div class="card-tools">
                <a href="{{ route('admin.sales.report') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-chart-bar"></i> Report
                </a>
                @can('create', App\Models\Sale::class)
                    <a href="{{ route('admin.sales.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> New Sale
                    </a>
                @endcan
            </div>
        </div>

        <div class="card-body">
            <table id="sales-table" class="table table-bordered table-striped" style="width: 100%">
                <thead>
                    <tr>
                        <th>Sale ID</th>
                        <th>Customer</th>
                        <th>Date</th>
                        <th>Total</th>
                        <th>Paid</th>
                        <th>Due</th>
                        <th>Status</th>
                        <th>Created By</th>
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
            ERP.serverTable('#sales-table', {
                url: '{{ route('admin.sales.index') }}',
                filter: '#sales-filter',
                count: '#sales-count',
                noun: 'sale',
                empty: 'No sales recorded yet.',
                order: [[2, 'desc']],
                columns: [
                    { data: 'sale_id', name: 'sale_id' },
                    { data: 'customer_label', name: 'customer_label' },
                    { data: 'sale_date', name: 'sale_date' },
                    { data: 'total_amount', name: 'total_amount' },
                    { data: 'paid_amount', name: 'paid_amount' },
                    { data: 'due_amount', name: 'due_amount' },
                    { data: 'state', name: 'state' },
                    { data: 'created_by_name', name: 'created_by_name' },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-nowrap' },
                ],
            });
        });
    </script>
@endpush
