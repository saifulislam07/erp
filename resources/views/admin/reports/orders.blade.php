@extends('layouts.admin')

@section('content_title', 'Order Report')

@section('content_body')
    @php
        $statuses = ['pending', 'processing', 'confirmed', 'on_delivery', 'delivered', 'rejected', 'cancelled'];
    @endphp

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Filters</h3>
        </div>
        <form action="{{ route('admin.reports.orders') }}" method="get">
            <div class="card-body row">
                <div class="col-md-3">
                    <label>From Date</label>
                    <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
                </div>
                <div class="col-md-3">
                    <label>To Date</label>
                    <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
                </div>
                <div class="col-md-3">
                    <label>Status</label>
                    <select name="status" class="form-control">
                        <option value="">-- All --</option>
                        @foreach ($statuses as $status)
                            <option value="{{ $status }}" {{ request('status') === $status ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label>Client</label>
                    <select name="client_id" class="form-control">
                        <option value="">-- All --</option>
                        @foreach ($clients as $client)
                            <option value="{{ $client->id }}" {{ (int) request('client_id') === $client->id ? 'selected' : '' }}>{{ $client->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-12 d-flex align-items-end justify-content-between mt-2">
                    @include('admin.partials.date-presets')
                    <div>
                        <button type="submit" class="btn btn-primary mr-2">Filter</button>
                        <a href="{{ route('admin.reports.orders.excel', request()->query()) }}" class="btn btn-success mr-2">Excel</a>
                        <a href="{{ route('admin.reports.orders.pdf', request()->query()) }}" class="btn btn-danger">PDF</a>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Orders</h3>
        </div>
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Client</th>
                        <th>Status</th>
                        <th>Total</th>
                        <th>Payment Method</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($orders as $order)
                        <tr>
                            <td><a href="{{ route('admin.orders.show', $order) }}">{{ $order->order_id }}</a></td>
                            <td>{{ $order->client?->name }}</td>
                            <td>{{ ucfirst(str_replace('_', ' ', $order->status)) }}</td>
                            <td>{{ money($order->total_amount) }}</td>
                            <td>{{ ucfirst(str_replace('_', ' ', $order->payment_method)) }}</td>
                            <td>{{ $order->created_at->format('Y-m-d') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
