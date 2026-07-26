@extends('layouts.admin')

@section('content_title', 'Purchase ' . $purchase->purchase_id)

@section('content_body')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Purchase {{ $purchase->purchase_id }}</h3>
            <div class="card-tools">
                <a href="{{ route('admin.purchases.returns.index', $purchase) }}" class="btn btn-secondary btn-sm">Returns</a>
                <a href="{{ route('admin.purchases.index') }}" class="btn btn-secondary btn-sm">Back</a>
            </div>
        </div>
        <div class="card-body">
            <table class="table table-sm">
                <tr><th>Supplier</th><td>{{ $purchase->supplier?->name }}</td></tr>
                <tr><th>Date</th><td>{{ $purchase->purchase_date->format('Y-m-d') }}</td></tr>
                <tr><th>Invoice #</th><td>{{ $purchase->invoice_number ?? '-' }}</td></tr>
                <tr><th>Payment Method</th><td>{{ ucfirst($purchase->payment_method) }}</td></tr>
                <tr><th>Status</th><td>{{ ucfirst($purchase->payment_status) }}</td></tr>
            </table>

            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Store</th>
                        <th>Qty</th>
                        <th>Price</th>
                        <th>VAT %</th>
                        <th>VAT Amount</th>
                        <th>Total</th>
                        <th>Expiry</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($purchase->items as $item)
                        <tr>
                            <td>{{ $item->product->name }}</td>
                            <td>{{ $item->store->name }}</td>
                            <td>{{ $item->quantity }}</td>
                            <td>{{ $item->purchase_price }}</td>
                            <td>{{ $item->vat_percentage }}</td>
                            <td>{{ $item->vat_amount }}</td>
                            <td>{{ $item->total_price }}</td>
                            <td>{{ $item->expiry_date?->format('Y-m-d') ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="6" class="text-right">Subtotal</th>
                        <th colspan="2">{{ $purchase->subtotal }}</th>
                    </tr>
                    <tr>
                        <th colspan="6" class="text-right">VAT</th>
                        <th colspan="2">{{ $purchase->vat_amount }}</th>
                    </tr>
                    <tr>
                        <th colspan="6" class="text-right">Grand Total</th>
                        <th colspan="2">{{ $purchase->total_amount }}</th>
                    </tr>
                    <tr>
                        <th colspan="6" class="text-right">Paid</th>
                        <th colspan="2">{{ $purchase->paid_amount }}</th>
                    </tr>
                    <tr>
                        <th colspan="6" class="text-right">Due</th>
                        <th colspan="2">{{ $purchase->due_amount }}</th>
                    </tr>
                </tfoot>
            </table>

            @if ($purchase->returns->isNotEmpty())
                <h5>Returns</h5>
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Return ID</th>
                            <th>Date</th>
                            <th>Reason</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($purchase->returns as $return)
                            <tr>
                                <td>{{ $return->return_id }}</td>
                                <td>{{ $return->return_date->format('Y-m-d') }}</td>
                                <td>{{ $return->reason }}</td>
                                <td>{{ $return->total_amount }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
@endsection
