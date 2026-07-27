@extends('layouts.admin')

@section('content_title', 'Sale ' . $sale->sale_id)

@section('content_body')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Sale {{ $sale->sale_id }}</h3>
            <div class="card-tools">
                @can('invoice.view')
                    <a href="{{ route('admin.invoices.sale', $sale) }}" class="btn btn-primary btn-sm" target="_blank">
                        <i class="fas fa-file-invoice"></i> Invoice
                    </a>
                    <a href="{{ route('admin.invoices.sale', ['sale' => $sale, 'format' => 'pdf']) }}" class="btn btn-danger btn-sm">
                        <i class="fas fa-file-pdf"></i> Download PDF
                    </a>
                @endcan
                <a href="{{ route('admin.sales.index') }}" class="btn btn-secondary btn-sm">Back</a>
            </div>
        </div>
        <div class="card-body">
            <table class="table table-sm">
                <tr><th>Customer</th><td>{{ $sale->customer_type === 'local' ? $sale->customer_name : $sale->customer?->name }}</td></tr>
                <tr><th>Customer Type</th><td>{{ ucfirst(str_replace('_', ' ', $sale->customer_type)) }}</td></tr>
                <tr><th>Date</th><td>{{ $sale->sale_date->format('Y-m-d') }}</td></tr>
                <tr><th>Payment Method</th><td>{{ ucfirst(str_replace('_', ' ', $sale->payment_method)) }}</td></tr>
                <tr><th>Transaction Reference</th><td>{{ $sale->transaction_reference ?? '-' }}</td></tr>
                <tr><th>Status</th><td>{{ ucfirst($sale->payment_status) }}</td></tr>
                <tr><th>Created By</th><td>{{ $sale->creator?->name }}</td></tr>
            </table>

            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Store</th>
                        <th>Qty</th>
                        <th>Unit Price</th>
                        <th>Discount</th>
                        <th>VAT %</th>
                        <th>VAT Amount</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($sale->items as $item)
                        <tr>
                            <td>{{ $item->product->name }}</td>
                            <td>{{ $item->store->name }}</td>
                            <td>{{ $item->quantity }}</td>
                            <td>{{ $item->unit_price }}</td>
                            <td>{{ $item->discount_amount }}</td>
                            <td>{{ $item->vat_percentage }}</td>
                            <td>{{ $item->vat_amount }}</td>
                            <td>{{ $item->total_price }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="7" class="text-right">Subtotal</th>
                        <th>{{ $sale->subtotal }}</th>
                    </tr>
                    <tr>
                        <th colspan="7" class="text-right">Discount</th>
                        <th>{{ $sale->discount_amount }}</th>
                    </tr>
                    <tr>
                        <th colspan="7" class="text-right">VAT</th>
                        <th>{{ $sale->vat_amount }}</th>
                    </tr>
                    <tr>
                        <th colspan="7" class="text-right">Grand Total</th>
                        <th>{{ $sale->total_amount }}</th>
                    </tr>
                    <tr>
                        <th colspan="7" class="text-right">Paid</th>
                        <th>{{ $sale->paid_amount }}</th>
                    </tr>
                    <tr>
                        <th colspan="7" class="text-right">Due</th>
                        <th>{{ $sale->due_amount }}</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
@endsection
