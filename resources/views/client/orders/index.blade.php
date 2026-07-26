@extends('layouts.client')

@section('title', 'My Orders')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">My Orders</h3>
            <div class="card-tools">
                <a href="{{ route('client.orders.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Place New Order
                </a>
            </div>
        </div>

        <div class="card-body">
            <form action="{{ route('client.orders.index') }}" method="get" class="form-inline mb-3">
                <select name="status" class="form-control mr-2">
                    <option value="">-- All Statuses --</option>
                    @foreach (['pending', 'processing', 'confirmed', 'on_delivery', 'delivered', 'rejected', 'cancelled'] as $status)
                        <option value="{{ $status }}" {{ request('status') === $status ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                    @endforeach
                </select>
                <input type="date" name="from_date" class="form-control mr-2" value="{{ request('from_date') }}">
                <input type="date" name="to_date" class="form-control mr-2" value="{{ request('to_date') }}">
                <button type="submit" class="btn btn-secondary">Filter</button>
            </form>

            @if ($orders->isEmpty())
                <p class="text-muted mb-0">You have not placed any orders yet.</p>
            @else
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Order ID</th>
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
                                <td>{{ $order->created_at->format('Y-m-d') }}</td>
                                <td>{{ $order->total_amount }}</td>
                                <td><span class="badge badge-{{ $badgeColors[$order->status] ?? 'secondary' }}">{{ ucfirst(str_replace('_', ' ', $order->status)) }}</span></td>
                                <td><a href="{{ route('client.orders.show', $order) }}" class="btn btn-sm btn-info">View</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
@endsection
