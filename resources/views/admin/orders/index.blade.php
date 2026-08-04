@extends('layouts.admin')

@section('content_title', 'Orders')

@section('content_body')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Filters</h3>
        </div>
        <form action="{{ route('admin.orders.index') }}" method="get" id="orders-filter" data-no-submit-guard>
            <div class="card-body row">
                <div class="col-md-2">
                    <label>Status</label>
                    <select name="status" class="form-control">
                        <option value="">-- All --</option>
                        @foreach (['pending', 'processing', 'confirmed', 'on_delivery', 'delivered', 'rejected', 'cancelled'] as $status)
                            <option value="{{ $status }}" {{ request('status') === $status ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label>Order ID</label>
                    <input type="text" name="order_id" class="form-control" value="{{ request('order_id') }}">
                </div>
                <div class="col-md-2">
                    <label>Client Name</label>
                    <input type="text" name="client_name" class="form-control" value="{{ request('client_name') }}">
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
            <h3 class="card-title" id="orders-count">All Orders</h3>
            <div class="card-tools">
                <a href="{{ route('admin.orders.pending') }}" class="btn btn-warning btn-sm">Pending Queue</a>
            </div>
        </div>

        <div class="card-body">
            <table id="orders-table" class="table table-bordered table-striped" style="width: 100%">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Client</th>
                        <th>Date</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
@endsection

@push('js')
    <script>
        $(function () {
            ERP.serverTable('#orders-table', {
                url: '{{ route('admin.orders.index') }}',
                filter: '#orders-filter',
                count: '#orders-count',
                noun: 'order',
                empty: 'No orders yet.',
                order: [[2, 'desc']],
                columns: [
                    { data: 'order_id', name: 'order_id' },
                    { data: 'client_label', name: 'client_label' },
                    { data: 'placed_on', name: 'placed_on' },
                    { data: 'total_amount', name: 'total_amount' },
                    { data: 'state', name: 'state' },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false },
                ],
            });
        });
    </script>
@endpush
