@extends('layouts.admin')

@section('content_title', 'Deliveries')

@section('content_body')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Filter</h3>
        </div>
        <form action="{{ route('admin.deliveries.index') }}" method="get">
            <div class="card-body row">
                <div class="col-md-3">
                    <select name="status" class="form-control">
                        <option value="">-- All Statuses --</option>
                        @foreach (['pending', 'out_for_delivery', 'delivered', 'failed'] as $status)
                            <option value="{{ $status }}" {{ request('status') === $status ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary">Filter</button>
                </div>
            </div>
        </form>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">All Deliveries</h3>
            <div class="card-tools">
                <a href="{{ route('admin.store.dispatch-queue') }}" class="btn btn-secondary btn-sm">Dispatch Queue</a>
            </div>
        </div>
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Client</th>
                        <th>Delivery Person</th>
                        <th>Status</th>
                        <th>Delivered At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($deliveries as $delivery)
                        <tr>
                            <td>{{ $delivery->order->order_id }}</td>
                            <td>{{ $delivery->order->client->name }}</td>
                            <td>{{ $delivery->delivery_person_name ?? '-' }}</td>
                            <td>
                                <span class="badge badge-{{ ['pending' => 'warning', 'out_for_delivery' => 'info', 'delivered' => 'success', 'failed' => 'danger'][$delivery->status] }}">
                                    {{ ucfirst(str_replace('_', ' ', $delivery->status)) }}
                                </span>
                            </td>
                            <td>{{ $delivery->delivered_at?->format('Y-m-d H:i') ?? '-' }}</td>
                            <td>
                                @if ($delivery->status === 'pending')
                                    <form action="{{ route('admin.deliveries.out', $delivery) }}" method="post" class="form-inline d-inline">
                                        @csrf
                                        <input type="text" name="delivery_person_name" class="form-control form-control-sm mr-1" placeholder="Delivery person">
                                        <button type="submit" class="btn btn-sm btn-info">Out for Delivery</button>
                                    </form>
                                @endif
                                @if ($delivery->status === 'out_for_delivery')
                                    <form action="{{ route('admin.deliveries.delivered', $delivery) }}" method="post" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success">Mark Delivered</button>
                                    </form>
                                    <button type="button" class="btn btn-sm btn-danger" data-toggle="modal" data-target="#fail-modal-{{ $delivery->id }}">Mark Failed</button>

                                    <div class="modal fade" id="fail-modal-{{ $delivery->id }}">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <form action="{{ route('admin.deliveries.failed', $delivery) }}" method="post">
                                                    @csrf
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Mark Delivery Failed</h5>
                                                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <textarea name="delivery_note" class="form-control" rows="3" placeholder="Reason" required></textarea>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="submit" class="btn btn-danger">Confirm Failed</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
