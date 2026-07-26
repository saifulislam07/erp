@extends('layouts.client')

@section('title', 'Dashboard')

@section('content')
    <div class="row">
        <div class="col-md-4">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $widgets['pending_orders'] }}</h3>
                    <p>Pending Orders</p>
                </div>
                <div class="icon"><i class="fas fa-clock"></i></div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $widgets['orders_in_delivery'] }}</h3>
                    <p>Orders In Delivery</p>
                </div>
                <div class="icon"><i class="fas fa-shipping-fast"></i></div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $widgets['unread_messages'] }}</h3>
                    <p>Unread Messages</p>
                </div>
                <div class="icon"><i class="fas fa-envelope"></i></div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Recent Order Activity</h3>
        </div>
        <div class="card-body p-0">
            @if ($recentOrders->isEmpty())
                <p class="p-3 text-muted mb-0">No orders yet.</p>
            @else
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($recentOrders as $order)
                            <tr>
                                <td>{{ $order->order_id }}</td>
                                <td>{{ $order->status }}</td>
                                <td>{{ $order->created_at }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
@endsection
