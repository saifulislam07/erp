@extends('layouts.admin')

@section('content_title', 'Edit Purchase')

@section('content_body')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Edit Purchase {{ $purchase->purchase_id }}</h3>
        </div>

        <form action="{{ route('admin.purchases.update', $purchase) }}" method="post" id="purchase-form">
            @csrf
            @method('PUT')
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="supplier_id">Supplier</label>
                            <select name="supplier_id" id="supplier_id" class="form-control @error('supplier_id') is-invalid @enderror">
                                <option value="">-- Select Supplier --</option>
                                @foreach ($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}" {{ (int) old('supplier_id', $purchase->supplier_id) === $supplier->id ? 'selected' : '' }}>
                                        {{ $supplier->name }} ({{ $supplier->unique_id }})
                                    </option>
                                @endforeach
                            </select>
                            @error('supplier_id')
                                <span class="invalid-feedback d-block">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="purchase_date">Purchase Date</label>
                            <input type="date" name="purchase_date" id="purchase_date"
                                class="form-control @error('purchase_date') is-invalid @enderror"
                                value="{{ old('purchase_date', $purchase->purchase_date->format('Y-m-d')) }}">
                            @error('purchase_date')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="invoice_number">Invoice Number</label>
                            <input type="text" name="invoice_number" id="invoice_number" class="form-control"
                                value="{{ old('invoice_number', $purchase->invoice_number) }}">
                        </div>
                    </div>
                </div>

                <hr>

                <table class="table table-bordered" id="items-table">
                    <thead>
                        <tr>
                            <th style="width: 25%">Product</th>
                            <th style="width: 15%">Store</th>
                            <th>Quantity</th>
                            <th>Purchase Price</th>
                            <th>VAT %</th>
                            <th>VAT Amount</th>
                            <th>Total</th>
                            <th>Expiry Date</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>

                <button type="button" class="btn btn-secondary btn-sm mt-2 mb-3" id="add-row">
                    <i class="fas fa-plus"></i> Add Product
                </button>

                <div class="row mt-4 justify-content-end">
                    <div class="col-md-4">
                        <table class="table table-sm">
                            <tr><th>Subtotal</th><td id="subtotal-display">0.00</td></tr>
                            <tr><th>Total VAT</th><td id="vat-display">0.00</td></tr>
                            <tr><th>Grand Total</th><td id="total-display">0.00</td></tr>
                        </table>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="paid_amount">Paid Amount</label>
                            <input type="number" step="0.01" name="paid_amount" id="paid_amount" class="form-control" value="{{ old('paid_amount', $purchase->paid_amount) }}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Due Amount</label>
                            <input type="text" id="due-display" class="form-control" value="0.00" disabled>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="payment_method">Payment Method</label>
                            <select name="payment_method" id="payment_method" class="form-control @error('payment_method') is-invalid @enderror">
                                <option value="cash" {{ old('payment_method', $purchase->payment_method) === 'cash' ? 'selected' : '' }}>Cash</option>
                                <option value="bank" {{ old('payment_method', $purchase->payment_method) === 'bank' ? 'selected' : '' }}>Bank</option>
                            </select>
                            @error('payment_method')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="note">Note</label>
                    <textarea name="note" id="note" rows="2" class="form-control">{{ old('note', $purchase->note) }}</textarea>
                </div>
            </div>

            <div class="card-footer">
                <button type="submit" class="btn btn-primary">Update Purchase</button>
                <a href="{{ route('admin.purchases.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
@endsection

@push('css')
    <style>
        .product-search-results {
            position: absolute;
            z-index: 1000;
            background: #fff;
            border: 1px solid #ced4da;
            width: 100%;
            max-height: 200px;
            overflow-y: auto;
        }
        .product-search-results a { display: block; padding: 5px 10px; }
        .product-search-results a:hover { background: #f1f1f1; }
    </style>
@endpush

@php
    $existingItemsData = $purchase->items->map(fn ($item) => [
        'product_id' => $item->product_id,
        'product_name' => $item->product->name,
        'store_id' => $item->store_id,
        'quantity' => $item->quantity,
        'purchase_price' => $item->purchase_price,
        'vat_percentage' => $item->vat_percentage,
        'expiry_date' => optional($item->expiry_date)->format('Y-m-d'),
    ]);
@endphp

@push('js')
    <script>
        $(function () {
            const storesOptions = `
                <option value="">-- Store --</option>
                @foreach ($stores as $store)
                    <option value="{{ $store->id }}">{{ $store->name }}</option>
                @endforeach
            `;
            const existingItems = @json($existingItemsData);
            let rowIndex = 0;

            function addRow(data) {
                data = data || {};
                const index = rowIndex++;
                const row = $(`
                    <tr data-index="${index}">
                        <td style="position: relative;">
                            <input type="hidden" name="items[${index}][product_id]" class="product-id" value="${data.product_id ?? ''}">
                            <input type="text" class="form-control product-search" placeholder="Search product..." value="${data.product_name ?? ''}">
                            <div class="product-search-results" style="display:none;"></div>
                        </td>
                        <td><select name="items[${index}][store_id]" class="form-control store-select">${storesOptions}</select></td>
                        <td><input type="number" step="0.01" name="items[${index}][quantity]" class="form-control quantity" value="${data.quantity ?? 1}"></td>
                        <td><input type="number" step="0.01" name="items[${index}][purchase_price]" class="form-control price" value="${data.purchase_price ?? 0}"></td>
                        <td><input type="number" step="0.01" name="items[${index}][vat_percentage]" class="form-control vat" value="${data.vat_percentage ?? 0}"></td>
                        <td><span class="vat-amount">0.00</span></td>
                        <td><span class="line-total">0.00</span></td>
                        <td><input type="date" name="items[${index}][expiry_date]" class="form-control" value="${data.expiry_date ?? ''}"></td>
                        <td><button type="button" class="btn btn-danger btn-sm remove-row"><i class="fas fa-trash"></i></button></td>
                    </tr>
                `);

                $('#items-table tbody').append(row);

                if (data.store_id) {
                    row.find('.store-select').val(data.store_id);
                }

                calculateRow(row);
            }

            $('#add-row').on('click', () => addRow());

            if (existingItems.length > 0) {
                existingItems.forEach((item) => addRow(item));
            } else {
                addRow();
            }

            calculateTotals();

            $('#items-table').on('input', '.quantity, .price, .vat', function () {
                calculateRow($(this).closest('tr'));
                calculateTotals();
            });

            $('#items-table').on('click', '.remove-row', function () {
                $(this).closest('tr').remove();
                calculateTotals();
            });

            $('#paid_amount').on('input', calculateTotals);

            function calculateRow(row) {
                const qty = parseFloat(row.find('.quantity').val()) || 0;
                const price = parseFloat(row.find('.price').val()) || 0;
                const vat = parseFloat(row.find('.vat').val()) || 0;
                const vatAmount = vat > 0 ? (price * qty * vat / 100) : 0;
                const total = (price * qty) + vatAmount;

                row.find('.vat-amount').text(vatAmount.toFixed(2));
                row.find('.line-total').text(total.toFixed(2));
            }

            function calculateTotals() {
                let subtotal = 0;
                let totalVat = 0;

                $('#items-table tbody tr').each(function () {
                    const qty = parseFloat($(this).find('.quantity').val()) || 0;
                    const price = parseFloat($(this).find('.price').val()) || 0;
                    const vatAmount = parseFloat($(this).find('.vat-amount').text()) || 0;

                    subtotal += qty * price;
                    totalVat += vatAmount;
                });

                const grandTotal = subtotal + totalVat;
                const paid = parseFloat($('#paid_amount').val()) || 0;
                const due = grandTotal - paid;

                $('#subtotal-display').text(subtotal.toFixed(2));
                $('#vat-display').text(totalVat.toFixed(2));
                $('#total-display').text(grandTotal.toFixed(2));
                $('#due-display').val(due.toFixed(2));
            }

            $('#items-table').on('input', '.product-search', function () {
                const input = $(this);
                const query = input.val();
                const resultsBox = input.siblings('.product-search-results');

                if (query.length < 2) {
                    resultsBox.hide().empty();
                    return;
                }

                $.get('{{ route('admin.products.search') }}', { q: query }, function (data) {
                    resultsBox.empty();

                    if (data.length === 0) {
                        resultsBox.hide();
                        return;
                    }

                    data.forEach((product) => {
                        const link = $(`<a href="#">${product.name} (${product.unique_id}) - Stock: ${product.stock_qty}</a>`);
                        link.on('click', function (e) {
                            e.preventDefault();
                            const row = input.closest('tr');
                            row.find('.product-id').val(product.id);
                            input.val(product.name);
                            row.find('.price').val(product.purchase_price ?? 0);
                            row.find('.vat').val(product.vat_percentage ?? 0);
                            resultsBox.hide().empty();
                            // .val() does not fire `input`, so recalculate explicitly
                            // or the row/footer totals would stay at 0.00.
                            calculateRow(row);
                            calculateTotals();
                        });
                        resultsBox.append(link);
                    });

                    resultsBox.show();
                });
            });

            $(document).on('click', function (e) {
                if (!$(e.target).hasClass('product-search')) {
                    $('.product-search-results').hide();
                }
            });
        });
    </script>
@endpush
