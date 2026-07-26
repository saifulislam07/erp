@extends('layouts.admin')

@section('content_title', 'Dispatch Queue')

@section('content_body')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Confirmed Orders Awaiting Dispatch</h3>
        </div>
        <div class="card-body">
            @if ($orders->isEmpty())
                <p class="text-muted mb-0">No orders waiting to be dispatched.</p>
            @else
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Client</th>
                            <th>Date</th>
                            <th>Total</th>
                            <th>Dispatch</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($orders as $order)
                            <tr>
                                <td>{{ $order->order_id }}</td>
                                <td>{{ $order->client->name }}</td>
                                <td>{{ $order->created_at->format('Y-m-d') }}</td>
                                <td>{{ $order->total_amount }}</td>
                                <td>
                                    <form action="{{ route('admin.store.dispatch', $order) }}" method="post" class="form-inline">
                                        @csrf
                                        <select name="store_id" class="form-control form-control-sm mr-1" required>
                                            <option value="">-- Store --</option>
                                            @foreach ($stores as $store)
                                                <option value="{{ $store->id }}">{{ $store->name }}</option>
                                            @endforeach
                                        </select>
                                        <input type="text" name="delivery_note" class="form-control form-control-sm mr-1" placeholder="Note (optional)">
                                        <button type="submit" class="btn btn-sm btn-primary">Send to Store</button>
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
