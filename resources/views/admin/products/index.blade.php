@extends('layouts.admin')

@section('content_title', 'Products')

@section('content_body')
    <form method="get" class="filter-bar" id="products-filter" data-no-submit-guard>
        <div class="row align-items-end">
            <div class="col-md-5">
                <div class="form-group">
                    <label for="q">Search</label>
                    <input type="text" name="q" id="q" class="form-control"
                        value="{{ request('q') }}" placeholder="Name or product ID">
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label for="category_id">Category</label>
                    <select name="category_id" id="category_id" class="form-control">
                        <option value="">All categories</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group page-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search mr-1"></i> Filter
                    </button>
                    <a href="#" class="btn btn-secondary" data-table-clear>Clear</a>
                </div>
            </div>
        </div>
    </form>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title" id="products-count">Loading…</h3>
            <div class="card-tools">
                <a href="{{ route('admin.products.report') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-chart-bar mr-1"></i> Report
                </a>
                <a href="{{ route('admin.products.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus mr-1"></i> Add product
                </a>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="products-table" class="table table-hover mb-0" style="width: 100%">
                    <thead>
                        <tr>
                            <th style="width: 52px"></th>
                            <th>Product</th>
                            <th>Category</th>
                            <th>Unit</th>
                            <th class="text-right">Purchase</th>
                            <th class="text-right">Sale</th>
                            <th class="text-right">Stock</th>
                            <th>Status</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script>
        $(function () {
            ERP.serverTable('#products-table', {
                url: '{{ route('admin.products.index') }}',
                filter: '#products-filter',
                count: '#products-count',
                noun: 'product',
                empty: 'No products yet — add your first one to get started.',
                order: [[1, 'asc']],
                columns: [
                    { data: 'thumb', name: 'image', orderable: false, searchable: false },
                    { data: 'product', name: 'name' },
                    { data: 'category_name', name: 'category_name' },
                    { data: 'unit_name', name: 'unit_name' },
                    { data: 'purchase_price', name: 'purchase_price', className: 'text-right' },
                    { data: 'price', name: 'price', className: 'text-right' },
                    { data: 'stock', name: 'stock', className: 'text-right' },
                    { data: 'state', name: 'state' },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-right text-nowrap' },
                ],
            });
        });
    </script>
@endpush
