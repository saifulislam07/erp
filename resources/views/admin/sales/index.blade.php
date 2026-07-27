@extends('layouts.admin')

@section('content_title', 'Sales')

@section('content_body')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Filters</h3>
        </div>
        <form action="{{ route('admin.sales.index') }}" method="get">
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
                </div>
            </div>
        </form>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">All Sales</h3>
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
            <table id="sales-table" class="table table-bordered table-striped">
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
                <tbody>
                    @foreach ($sales as $sale)
                        <tr>
                            <td>{{ $sale->sale_id }}</td>
                            <td>{{ $sale->customer_type === 'local' ? $sale->customer_name : $sale->customer?->name }}</td>
                            <td>{{ $sale->sale_date->format('Y-m-d') }}</td>
                            <td>{{ $sale->total_amount }}</td>
                            <td>{{ $sale->paid_amount }}</td>
                            <td>{{ $sale->due_amount }}</td>
                            <td>
                                <span class="badge badge-{{ $sale->payment_status === 'paid' ? 'success' : ($sale->payment_status === 'partial' ? 'warning' : 'danger') }}">
                                    {{ ucfirst($sale->payment_status) }}
                                </span>
                            </td>
                            <td>{{ $sale->creator?->name }}</td>
                            <td>
                                <a href="{{ route('admin.sales.show', $sale) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @can('invoice.view')
                                    <a href="{{ route('admin.invoices.sale', $sale) }}" class="btn btn-sm btn-secondary"
                                        target="_blank" title="View invoice">
                                        <i class="fas fa-file-invoice"></i>
                                    </a>
                                @endcan
                                @can('update', $sale)
                                    <a href="{{ route('admin.sales.edit', $sale) }}" class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                @endcan
                                @can('delete', $sale)
                                    <form action="{{ route('admin.sales.destroy', $sale) }}" method="post" class="d-inline delete-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('js')
    <script>
        $(function () {
            $('#sales-table').DataTable();

            $('.delete-form').on('submit', function (e) {
                e.preventDefault();
                const form = this;
                Swal.fire({
                    title: 'Are you sure?',
                    text: 'This will reverse the stock and payment for this sale.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, delete it',
                }).then((result) => {
                    if (result.isConfirmed) form.submit();
                });
            });
        });
    </script>
@endpush
