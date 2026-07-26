@extends('layouts.client')

@section('title', 'My Returns')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">My Returns</h3>
        </div>
        <div class="card-body">
            @if ($returns->isEmpty())
                <p class="text-muted mb-0">You have not requested any returns yet. You can request a return from a delivered order's detail page.</p>
            @else
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Return ID</th>
                            <th>Order</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Refund</th>
                            <th>Requested At</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($returns as $return)
                            <tr>
                                <td>{{ $return->return_id }}</td>
                                <td>{{ $return->order->order_id }}</td>
                                <td>{{ $return->returnType->name }}</td>
                                <td>
                                    <span class="badge badge-{{ ['pending' => 'warning', 'approved' => 'success', 'rejected' => 'danger'][$return->status] }}">
                                        {{ ucfirst($return->status) }}
                                    </span>
                                </td>
                                <td>{{ $return->refund_amount ?? '-' }}</td>
                                <td>{{ $return->requested_at->format('Y-m-d') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
@endsection
