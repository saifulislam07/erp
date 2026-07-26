@extends('layouts.admin')

@section('content_title', 'Return ' . $return->return_id)

@section('content_body')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Return {{ $return->return_id }}</h3>
            <div class="card-tools">
                <a href="{{ route('admin.returns.index') }}" class="btn btn-secondary btn-sm">Back</a>
            </div>
        </div>
        <div class="card-body">
            <table class="table table-sm">
                <tr><th>Order</th><td>{{ $return->order->order_id }}</td></tr>
                <tr><th>Client</th><td>{{ $return->client->name }} ({{ $return->client->unique_id }})</td></tr>
                <tr><th>Return Type</th><td>{{ $return->returnType->name }} ({{ $return->returnType->disposition === 'restock' ? 'Restock' : 'Damage Section' }})</td></tr>
                <tr><th>Reason</th><td>{{ $return->reason }}</td></tr>
                <tr><th>Status</th><td>{{ ucfirst($return->status) }}</td></tr>
                @if ($return->status !== 'pending')
                    <tr><th>Note</th><td>{{ $return->note }}</td></tr>
                @endif
                @if ($return->status === 'approved')
                    <tr><th>Refund Amount</th><td>{{ $return->refund_amount }}</td></tr>
                    <tr><th>Refund Method</th><td>{{ ucfirst(str_replace('_', ' ', $return->refund_method)) }}</td></tr>
                @endif
            </table>

            <table class="table table-bordered">
                <thead>
                    <tr><th>Product</th><th>Qty</th><th>Unit Price</th><th>Total</th></tr>
                </thead>
                <tbody>
                    @foreach ($return->items as $item)
                        <tr>
                            <td>{{ $item->product->name }}</td>
                            <td>{{ $item->quantity }}</td>
                            <td>{{ $item->unit_price }}</td>
                            <td>{{ $item->total_price }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="3" class="text-right">Suggested Refund (product price only)</th>
                        <th>{{ $return->items->sum('total_price') }}</th>
                    </tr>
                </tfoot>
            </table>

            @if ($return->status === 'pending')
                <form action="{{ route('admin.returns.approve', $return) }}" method="post" class="mb-3">
                    @csrf
                    <div class="form-row align-items-end">
                        <div class="col-md-3">
                            <label>Refund Amount</label>
                            <input type="number" step="0.01" name="refund_amount" class="form-control" value="{{ $return->items->sum('total_price') }}" required>
                        </div>
                        <div class="col-md-3">
                            <label>Refund Method</label>
                            <select name="refund_method" class="form-control" required>
                                <option value="cash">Cash</option>
                                <option value="bank">Bank</option>
                                <option value="mobile_banking">Mobile Banking</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-success">Approve Return</button>
                        </div>
                    </div>
                </form>

                <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#reject-modal">Reject Return</button>

                <div class="modal fade" id="reject-modal">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form action="{{ route('admin.returns.reject', $return) }}" method="post">
                                @csrf
                                <div class="modal-header">
                                    <h5 class="modal-title">Reject Return</h5>
                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                </div>
                                <div class="modal-body">
                                    <textarea name="note" class="form-control" rows="3" placeholder="Reason" required></textarea>
                                </div>
                                <div class="modal-footer">
                                    <button type="submit" class="btn btn-danger">Reject</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
