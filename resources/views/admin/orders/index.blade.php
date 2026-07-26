@extends('layouts.admin')

@section('content_title', 'Orders')

@section('content_body')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Filters</h3>
        </div>
        <form action="{{ route('admin.orders.index') }}" method="get">
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
                </div>
            </div>
        </form>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">All Orders</h3>
            <div class="card-tools">
                <a href="{{ route('admin.orders.pending') }}" class="btn btn-warning btn-sm">Pending Queue</a>
            </div>
        </div>

        <div class="card-body">
            <table id="orders-table" class="table table-bordered table-striped">
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
                <tbody>
                    @foreach ($orders as $order)
                        @php
                            $badgeColors = [
                                'pending' => 'warning', 'processing' => 'info', 'confirmed' => 'primary',
                                'on_delivery' => 'secondary', 'delivered' => 'success', 'rejected' => 'danger', 'cancelled' => 'dark',
                            ];
                        @endphp
                        <tr>
                            <td>{{ $order->order_id }}</td>
                            <td>{{ $order->client->name }} ({{ $order->client->unique_id }})</td>
                            <td>{{ $order->created_at->format('Y-m-d') }}</td>
                            <td>{{ $order->total_amount }}</td>
                            <td><span class="badge badge-{{ $badgeColors[$order->status] ?? 'secondary' }}">{{ ucfirst(str_replace('_', ' ', $order->status)) }}</span></td>
                            <td><a href="{{ route('admin.orders.show', $order) }}" class="btn btn-sm btn-info">View</a></td>
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
            $('#orders-table').DataTable();
        });
    </script>
@endpush
