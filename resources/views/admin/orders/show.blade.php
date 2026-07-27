@extends('layouts.admin')

@section('content_title', 'Order ' . $order->order_id)

@section('content_body')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Order {{ $order->order_id }}</h3>
            <div class="card-tools">
                @can('invoice.view')
                    <a href="{{ route('admin.invoices.order', $order) }}" class="btn btn-primary btn-sm" target="_blank">
                        <i class="fas fa-file-invoice"></i> Invoice
                    </a>
                    <a href="{{ route('admin.invoices.order', ['order' => $order, 'format' => 'pdf']) }}" class="btn btn-danger btn-sm">
                        <i class="fas fa-file-pdf"></i> Download PDF
                    </a>
                @endcan
                <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary btn-sm">Back</a>
            </div>
        </div>
        <div class="card-body">
            <table class="table table-sm">
                <tr><th>Client</th><td>{{ $order->client->name }} ({{ $order->client->unique_id }})</td></tr>
                <tr><th>Status</th><td>{{ ucfirst(str_replace('_', ' ', $order->status)) }}</td></tr>
                <tr><th>Payment Method</th><td>{{ ucfirst(str_replace('_', ' ', $order->payment_method)) }}</td></tr>
                <tr><th>Transaction Reference</th><td>{{ $order->transaction_reference ?? '-' }}</td></tr>
                <tr>
                    <th>Payment Receipt</th>
                    <td>
                        @if ($order->payment_receipt)
                            <a href="{{ media_url($order->payment_receipt) }}" target="_blank">View Receipt</a>
                        @else
                            -
                        @endif
                    </td>
                </tr>
                <tr><th>Note</th><td>{{ $order->note ?? '-' }}</td></tr>
                <tr><th>Admin Note</th><td>{{ $order->admin_note ?? '-' }}</td></tr>
                @if ($order->cancel_reason)
                    <tr><th>Cancel Reason</th><td>{{ $order->cancel_reason }}</td></tr>
                @endif
            </table>

            <table class="table table-bordered">
                <thead>
                    <tr><th>Product</th><th>Qty</th><th>Unit Price</th><th>Discount</th><th>VAT</th><th>Total</th></tr>
                </thead>
                <tbody>
                    @foreach ($order->items as $item)
                        <tr>
                            <td>{{ $item->product->name }}</td>
                            <td>{{ $item->quantity }}</td>
                            <td>{{ $item->unit_price }}</td>
                            <td>{{ $item->discount_amount }}</td>
                            <td>{{ $item->vat_amount }}</td>
                            <td>{{ $item->total_price }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr><th colspan="5" class="text-right">Subtotal</th><th>{{ $order->subtotal }}</th></tr>
                    <tr><th colspan="5" class="text-right">Discount</th><th>{{ $order->discount_amount }}</th></tr>
                    <tr><th colspan="5" class="text-right">VAT</th><th>{{ $order->vat_amount }}</th></tr>
                    <tr><th colspan="5" class="text-right">Delivery Charge</th><th>{{ $order->delivery_charge }}</th></tr>
                    <tr><th colspan="5" class="text-right">Grand Total</th><th>{{ $order->total_amount }}</th></tr>
                </tfoot>
            </table>

            <div class="d-flex" style="gap: 8px;">
                @if ($order->status === 'pending')
                    <form action="{{ route('admin.orders.accept', $order) }}" method="post">
                        @csrf
                        <button type="submit" class="btn btn-success">Accept Order</button>
                    </form>

                    <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#reject-modal">Reject Order</button>
                @endif

                @if ($nextStatus)
                    <form action="{{ route('admin.orders.update-status', $order) }}" method="post">
                        @csrf
                        <input type="hidden" name="status" value="{{ $nextStatus }}">
                        <button type="submit" class="btn btn-primary">
                            Move to {{ ucfirst(str_replace('_', ' ', $nextStatus)) }}
                        </button>
                    </form>
                @endif
            </div>

            @if ($order->status === 'pending')
                <div class="modal fade" id="reject-modal">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form action="{{ route('admin.orders.reject', $order) }}" method="post">
                                @csrf
                                <div class="modal-header">
                                    <h5 class="modal-title">Reject Order</h5>
                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                </div>
                                <div class="modal-body">
                                    <div class="form-group">
                                        <label>Reason</label>
                                        <textarea name="reason" class="form-control" rows="3" required></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="submit" class="btn btn-danger">Reject</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endif

            @if ($order->packaging)
                <hr>
                <h5>Packaging</h5>
                <p>
                    Packed by {{ $order->packaging->packer->name ?? '-' }} at {{ $order->packaging->packed_at->format('Y-m-d H:i') }}<br>
                    Notes: {{ $order->packaging->notes ?? '-' }}
                </p>
                <form action="{{ route('admin.orders.pack', $order) }}" method="post" class="form-inline">
                    @csrf
                    <input type="text" name="notes" class="form-control mr-2" placeholder="Update packaging notes" value="{{ $order->packaging->notes }}">
                    <button type="submit" class="btn btn-secondary btn-sm">Update</button>
                </form>
            @endif
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Status Timeline</h3>
        </div>
        <div class="card-body">
            <ul class="list-unstyled">
                @foreach ($order->statusLogs as $log)
                    <li class="mb-2">
                        <strong>{{ ucfirst(str_replace('_', ' ', $log->to_status)) }}</strong>
                        <span class="text-muted">by {{ ucfirst($log->changed_by_type) }} on {{ $log->created_at->format('Y-m-d H:i') }}</span>
                        @if ($log->note)
                            <br><small>{{ $log->note }}</small>
                        @endif
                    </li>
                @endforeach
            </ul>
        </div>
    </div>

    @if ($order->feedbacks->isNotEmpty())
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Client Feedback</h3>
            </div>
            <div class="card-body">
                @foreach ($order->feedbacks as $feedback)
                    <p><strong>{{ ucfirst($feedback->type) }}:</strong> {{ $feedback->rating }}/5 - {{ $feedback->comment }}</p>
                @endforeach
            </div>
        </div>
    @endif
@endsection
