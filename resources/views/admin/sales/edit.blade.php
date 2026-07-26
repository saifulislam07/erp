@extends('layouts.admin')

@section('content_title', 'Edit Sale')

@php
    $existingItemsData = $sale->items->map(fn ($item) => [
        'product_id' => $item->product_id,
        'store_id' => $item->store_id,
        'quantity' => $item->quantity,
        'unit_price' => $item->unit_price,
        'discount_amount' => $item->discount_amount,
        'vat_percentage' => $item->vat_percentage,
    ]);

    $existingProductNames = $sale->items->mapWithKeys(fn ($item) => [$item->product_id => $item->product->name]);
@endphp

@section('content_body')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Edit Sale {{ $sale->sale_id }}</h3>
        </div>

        <form action="{{ route('admin.sales.update', $sale) }}" method="post" id="sale-form">
            @csrf
            @method('PUT')
            <div class="card-body">
                <div class="form-group">
                    <label>Customer Type</label>
                    <div>
                        <div class="form-check form-check-inline">
                            <input type="radio" name="customer_type" value="local" id="type_local" class="form-check-input" {{ $sale->customer_type === 'local' ? 'checked' : '' }}>
                            <label class="form-check-label" for="type_local">Local</label>
                        </div>
                        @if ($canSellToClientAgent)
                            <div class="form-check form-check-inline">
                                <input type="radio" name="customer_type" value="client_agent" id="type_client" class="form-check-input" {{ $sale->customer_type === 'client_agent' ? 'checked' : '' }}>
                                <label class="form-check-label" for="type_client">Client / Agent</label>
                            </div>
                        @endif
                    </div>
                    @error('customer_type')
                        <span class="text-danger d-block">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group" id="local-customer-field">
                    <label for="customer_name">Customer Name</label>
                    <input type="text" name="customer_name" id="customer_name" class="form-control @error('customer_name') is-invalid @enderror"
                        value="{{ old('customer_name', $sale->customer_name) }}">
                    @error('customer_name')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group" id="client-customer-field" style="display:none; position: relative;">
                    <label for="customer_search">Client / Agent</label>
                    <input type="hidden" name="customer_id" id="customer_id" value="{{ old('customer_id', $sale->customer_id) }}">
                    <input type="text" id="customer_search" class="form-control @error('customer_id') is-invalid @enderror"
                        placeholder="Search by name or ID..." value="{{ $sale->customer?->name }}">
                    <div id="customer-search-results" class="product-search-results" style="display:none;"></div>
                    @error('customer_id')
                        <span class="invalid-feedback d-block">{{ $message }}</span>
                    @enderror
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="sale_date">Sale Date</label>
                            <input type="date" name="sale_date" id="sale_date"
                                class="form-control @error('sale_date') is-invalid @enderror"
                                value="{{ old('sale_date', $sale->sale_date->format('Y-m-d')) }}">
                            @error('sale_date')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <hr>

                <table class="table table-bordered" id="items-table">
                    <thead>
                        <tr>
                            <th style="width: 22%">Product</th>
                            <th style="width: 13%">Store</th>
                            <th>Quantity</th>
                            <th>Unit Price</th>
                            @if ($canApplyDiscount)
                                <th>Discount</th>
                            @endif
                            <th>VAT %</th>
                            <th>VAT Amount</th>
                            <th>Total</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>

                <button type="button" class="btn btn-secondary btn-sm" id="add-row">
                    <i class="fas fa-plus"></i> Add Product
                </button>

                <div class="row mt-4 justify-content-end">
                    <div class="col-md-4">
                        <table class="table table-sm">
                            <tr><th>Subtotal</th><td id="subtotal-display">0.00</td></tr>
                            @if ($canApplyDiscount)
                                <tr><th>Total Discount</th><td id="discount-display">0.00</td></tr>
                            @endif
                            <tr><th>Total VAT</th><td id="vat-display">0.00</td></tr>
                            <tr><th>Grand Total</th><td id="total-display">0.00</td></tr>
                        </table>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="paid_amount">Paid Amount</label>
                            <input type="number" step="0.01" name="paid_amount" id="paid_amount" class="form-control" value="{{ old('paid_amount', $sale->paid_amount) }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Due Amount</label>
                            <input type="text" id="due-display" class="form-control" value="0.00" disabled>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="payment_method">Payment Method</label>
                            <select name="payment_method" id="payment_method" class="form-control @error('payment_method') is-invalid @enderror">
                                <option value="cash" {{ $sale->payment_method === 'cash' ? 'selected' : '' }}>Cash</option>
                                <option value="bank" {{ $sale->payment_method === 'bank' ? 'selected' : '' }}>Bank</option>
                                <option value="mobile_banking" {{ $sale->payment_method === 'mobile_banking' ? 'selected' : '' }}>Mobile Banking</option>
                            </select>
                            @error('payment_method')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="transaction_reference">Transaction Reference</label>
                            <input type="text" name="transaction_reference" id="transaction_reference" class="form-control" value="{{ old('transaction_reference', $sale->transaction_reference) }}">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="note">Note</label>
                    <textarea name="note" id="note" rows="2" class="form-control">{{ old('note', $sale->note) }}</textarea>
                </div>
            </div>

            <div class="card-footer">
                <button type="submit" class="btn btn-primary">Update Sale</button>
                <a href="{{ route('admin.sales.index') }}" class="btn btn-secondary">Cancel</a>
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

@push('js')
    <script>
        $(function () {
            const canApplyDiscount = @json($canApplyDiscount);
            const storesOptions = `
                <option value="">-- Store --</option>
                @foreach ($stores as $store)
                    <option value="{{ $store->id }}">{{ $store->name }}</option>
                @endforeach
            `;
            const existingItems = @json($existingItemsData);
            const existingProductNames = @json($existingProductNames);
            let rowIndex = 0;

            function toggleCustomerFields() {
                const type = $('input[name=customer_type]:checked').val();
                $('#local-customer-field').toggle(type === 'local');
                $('#client-customer-field').toggle(type === 'client_agent');
            }

            $('input[name=customer_type]').on('change', toggleCustomerFields);
            toggleCustomerFields();

            $('#customer_search').on('input', function () {
                const input = $(this);
                const query = input.val();
                const resultsBox = $('#customer-search-results');

                if (query.length < 2) {
                    resultsBox.hide().empty();
                    return;
                }

                $.get('{{ route('admin.clients.search') }}', { q: query }, function (data) {
                    resultsBox.empty();

                    if (data.length === 0) {
                        resultsBox.hide();
                        return;
                    }

                    data.forEach((client) => {
                        const link = $(`<a href="#">${client.name} (${client.unique_id}) - ${client.type}</a>`);
                        link.on('click', function (e) {
                            e.preventDefault();
                            $('#customer_id').val(client.id);
                            input.val(`${client.name} (${client.unique_id})`);
                            resultsBox.hide().empty();
                        });
                        resultsBox.append(link);
                    });

                    resultsBox.show();
                });
            });

            function addRow(data) {
                data = data || {};
                const index = rowIndex++;
                const discountCell = canApplyDiscount
                    ? `<td><input type="number" step="0.01" name="items[${index}][discount_amount]" class="form-control discount" value="${data.discount_amount ?? 0}"></td>`
                    : '';
                const productName = data.product_id ? (existingProductNames[data.product_id] ?? '') : '';

                const row = $(`
                    <tr data-index="${index}">
                        <td style="position: relative;">
                            <input type="hidden" name="items[${index}][product_id]" class="product-id" value="${data.product_id ?? ''}">
                            <input type="text" class="form-control product-search" placeholder="Search product..." value="${productName}">
                            <div class="product-search-results" style="display:none;"></div>
                        </td>
                        <td><select name="items[${index}][store_id]" class="form-control store-select">${storesOptions}</select></td>
                        <td><input type="number" step="0.01" name="items[${index}][quantity]" class="form-control quantity" value="${data.quantity ?? 1}"></td>
                        <td><input type="number" step="0.01" name="items[${index}][unit_price]" class="form-control price" value="${data.unit_price ?? 0}"></td>
                        ${discountCell}
                        <td><input type="number" step="0.01" name="items[${index}][vat_percentage]" class="form-control vat" value="${data.vat_percentage ?? 0}"></td>
                        <td><span class="vat-amount">0.00</span></td>
                        <td><span class="line-total">0.00</span></td>
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

            $('#items-table').on('input', '.quantity, .price, .vat, .discount', function () {
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
                const discount = parseFloat(row.find('.discount').val()) || 0;
                const vat = parseFloat(row.find('.vat').val()) || 0;
                const vatAmount = vat > 0 ? (price * qty * vat / 100) : 0;
                const total = (price * qty) - discount + vatAmount;

                row.find('.vat-amount').text(vatAmount.toFixed(2));
                row.find('.line-total').text(total.toFixed(2));
            }

            function calculateTotals() {
                let subtotal = 0;
                let totalDiscount = 0;
                let totalVat = 0;

                $('#items-table tbody tr').each(function () {
                    const qty = parseFloat($(this).find('.quantity').val()) || 0;
                    const price = parseFloat($(this).find('.price').val()) || 0;
                    const discount = parseFloat($(this).find('.discount').val()) || 0;
                    const vatAmount = parseFloat($(this).find('.vat-amount').text()) || 0;

                    subtotal += qty * price;
                    totalDiscount += discount;
                    totalVat += vatAmount;
                });

                const grandTotal = subtotal - totalDiscount + totalVat;
                const paid = parseFloat($('#paid_amount').val()) || 0;
                const due = grandTotal - paid;

                $('#subtotal-display').text(subtotal.toFixed(2));
                $('#discount-display').text(totalDiscount.toFixed(2));
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
                            row.find('.price').val(product.sale_price ?? 0);
                            row.find('.vat').val(product.vat_percentage ?? 0);
                            calculateRow(row);
                            calculateTotals();
                            resultsBox.hide().empty();
                        });
                        resultsBox.append(link);
                    });

                    resultsBox.show();
                });
            });

            $(document).on('click', function (e) {
                if (!$(e.target).hasClass('product-search') && e.target.id !== 'customer_search') {
                    $('.product-search-results').hide();
                    $('#customer-search-results').hide();
                }
            });
        });
    </script>
@endpush
