@extends('layouts.client')

@section('title', 'Order ' . $order->order_id)

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Order {{ $order->order_id }}</h3>
            <div class="card-tools">
                <a href="{{ route('client.orders.index') }}" class="btn btn-secondary btn-sm">Back</a>
            </div>
        </div>
        <div class="card-body">
            <p><strong>Status:</strong> {{ ucfirst(str_replace('_', ' ', $order->status)) }}</p>
            <p><strong>Payment Method:</strong> {{ ucfirst(str_replace('_', ' ', $order->payment_method)) }}</p>

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

            @if (in_array($order->status, ['pending', 'processing']))
                <form action="{{ route('client.orders.cancel', $order) }}" method="post" id="cancel-form">
                    @csrf
                    <div class="form-group">
                        <label for="cancel_reason">Cancel this order</label>
                        <textarea name="cancel_reason" id="cancel_reason" rows="2" class="form-control @error('cancel_reason') is-invalid @enderror">{{ old('cancel_reason') }}</textarea>
                        @error('cancel_reason')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                    <button type="submit" class="btn btn-danger btn-sm">Cancel Order</button>
                </form>
            @endif

            @if ($order->status === 'delivered')
                <hr>
                <a href="{{ route('client.returns.create', $order) }}" class="btn btn-warning btn-sm">Request Return</a>

                <hr>
                <h5>Leave Feedback</h5>
                <form action="{{ route('client.orders.feedback', $order) }}" method="post">
                    @csrf
                    @foreach (['product' => 'Product Quality', 'delivery' => 'Delivery Experience', 'agent' => 'Agent Service'] as $type => $label)
                        <div class="form-group">
                            <label>{{ $label }} Rating</label>
                            <select name="{{ $type }}_rating" class="form-control">
                                <option value="">-- No rating --</option>
                                @for ($i = 1; $i <= 5; $i++)
                                    <option value="{{ $i }}">{{ $i }}</option>
                                @endfor
                            </select>
                            <textarea name="{{ $type }}_comment" class="form-control mt-1" rows="1" placeholder="Comment (optional)"></textarea>
                        </div>
                    @endforeach
                    @error('product_rating')
                        <div class="alert alert-danger">{{ $message }}</div>
                    @enderror
                    <button type="submit" class="btn btn-primary btn-sm">Submit Feedback</button>
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
@endsection

@push('js')
    <script>
        $(function () {
            $('#cancel-form').on('submit', function (e) {
                if (!confirm('Are you sure you want to cancel this order?')) {
                    e.preventDefault();
                }
            });
        });
    </script>
@endpush
