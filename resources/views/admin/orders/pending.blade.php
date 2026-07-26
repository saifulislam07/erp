@extends('layouts.admin')

@section('content_title', 'Pending Orders')

@section('content_body')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Pending Orders</h3>
            <div class="card-tools">
                <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary btn-sm">All Orders</a>
            </div>
        </div>

        <div class="card-body">
            @if ($orders->isEmpty())
                <p class="text-muted mb-0">No pending orders.</p>
            @else
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Client</th>
                            <th>Date</th>
                            <th>Total</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($orders as $order)
                            <tr>
                                <td>{{ $order->order_id }}</td>
                                <td>{{ $order->client->name }}</td>
                                <td>{{ $order->created_at->format('Y-m-d H:i') }}</td>
                                <td>{{ $order->total_amount }}</td>
                                <td>
                                    <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-sm btn-info">View</a>
                                    <form action="{{ route('admin.orders.accept', $order) }}" method="post" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success">Accept</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
@endsection
