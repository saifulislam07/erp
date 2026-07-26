@extends('layouts.client')

@section('title', 'Place New Order')

@section('content')
    <div class="row">
        <div class="col-md-7">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Product Catalog</h3>
                    <div class="card-tools">
                        <input type="text" id="catalog-filter" class="form-control form-control-sm" placeholder="Filter products...">
                    </div>
                </div>
                <div class="card-body" style="max-height: 600px; overflow-y: auto;">
                    <table class="table table-hover" id="catalog-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Qty</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($products as $product)
                                <tr class="catalog-row" data-name="{{ strtolower($product->name) }}">
                                    <td>{{ $product->name }}</td>
                                    <td>{{ $product->category?->name }}</td>
                                    <td>{{ $product->sale_price }} / {{ $product->unit?->symbol }}</td>
                                    <td><input type="number" min="1" value="1" class="form-control form-control-sm add-qty" style="width: 70px;"></td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-primary add-to-cart"
                                            data-id="{{ $product->id }}" data-name="{{ $product->name }}"
                                            data-price="{{ $product->sale_price }}">
                                            Add
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-5">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Your Cart</h3>
                </div>
                <form action="{{ route('client.orders.store') }}" method="post" enctype="multipart/form-data" id="order-form">
                    @csrf
                    <div class="card-body">
                        <table class="table table-sm" id="cart-table">
                            <thead>
                                <tr><th>Product</th><th>Qty</th><th>Subtotal</th><th></th></tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                        <p id="empty-cart-msg" class="text-muted">Your cart is empty.</p>

                        <h5>Total (est.): <span id="cart-total">0.00</span></h5>

                        <hr>

                        <div class="form-group">
                            <label for="payment_method">Payment Method</label>
                            <select name="payment_method" id="payment_method" class="form-control @error('payment_method') is-invalid @enderror">
                                <option value="cash_on_delivery">Cash on Delivery</option>
                                <option value="bank">Bank Transfer</option>
                                <option value="mobile_banking">Mobile Banking</option>
                            </select>
                            @error('payment_method')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group" id="receipt-field" style="display:none;">
                            <label for="payment_receipt">Payment Receipt</label>
                            <input type="file" name="payment_receipt" id="payment_receipt" class="form-control-file @error('payment_receipt') is-invalid @enderror">
                            @error('payment_receipt')
                                <span class="invalid-feedback d-block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group" id="reference-field" style="display:none;">
                            <label for="transaction_reference">Transaction Reference</label>
                            <input type="text" name="transaction_reference" id="transaction_reference" class="form-control @error('transaction_reference') is-invalid @enderror" value="{{ old('transaction_reference') }}">
                            @error('transaction_reference')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="note">Note</label>
                            <textarea name="note" id="note" rows="2" class="form-control">{{ old('note') }}</textarea>
                        </div>

                        @error('items')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary" id="submit-order" disabled>Place Order</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script>
        $(function () {
            const cart = {};

            $('#catalog-filter').on('input', function () {
                const term = $(this).val().toLowerCase();
                $('.catalog-row').each(function () {
                    $(this).toggle($(this).data('name').includes(term));
                });
            });

            $('.add-to-cart').on('click', function () {
                const btn = $(this);
                const id = btn.data('id');
                const qty = parseFloat(btn.closest('tr').find('.add-qty').val()) || 1;

                cart[id] = {
                    id: id,
                    name: btn.data('name'),
                    price: parseFloat(btn.data('price')),
                    qty: (cart[id] ? cart[id].qty : 0) + qty,
                };

                renderCart();
            });

            function renderCart() {
                const tbody = $('#cart-table tbody');
                tbody.empty();
                let total = 0;
                const items = Object.values(cart);

                items.forEach((item) => {
                    const lineTotal = item.qty * item.price;
                    total += lineTotal;

                    tbody.append(`
                        <tr>
                            <td>
                                ${item.name}
                                <input type="hidden" name="items[${item.id}][product_id]" value="${item.id}">
                                <input type="hidden" name="items[${item.id}][quantity]" value="${item.qty}">
                            </td>
                            <td>${item.qty}</td>
                            <td>${lineTotal.toFixed(2)}</td>
                            <td><button type="button" class="btn btn-xs btn-danger remove-item" data-id="${item.id}">&times;</button></td>
                        </tr>
                    `);
                });

                $('#empty-cart-msg').toggle(items.length === 0);
                $('#cart-total').text(total.toFixed(2) + ' (+ delivery charge)');
                $('#submit-order').prop('disabled', items.length === 0);
            }

            $('#cart-table').on('click', '.remove-item', function () {
                delete cart[$(this).data('id')];
                renderCart();
            });

            $('#payment_method').on('change', function () {
                const method = $(this).val();
                $('#receipt-field').toggle(method === 'bank');
                $('#reference-field').toggle(method === 'mobile_banking');
            });
        });
    </script>
@endpush
