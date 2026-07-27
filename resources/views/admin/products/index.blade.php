@extends('layouts.admin')

@section('content_title', 'Products')

@section('content_body')
    <form method="get" class="filter-bar" data-no-submit-guard>
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
                    @if (request()->hasAny(['q', 'category_id']))
                        <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">Clear</a>
                    @endif
                </div>
            </div>
        </div>
    </form>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">{{ $products->count() }} {{ Str::plural('product', $products->count()) }}</h3>
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
            @if ($products->isEmpty())
                <div class="empty-state">
                    <i class="fas fa-box-open"></i>
                    <p>
                        @if (request()->hasAny(['q', 'category_id']))
                            No products match this filter.
                        @else
                            No products yet — add your first one to get started.
                        @endif
                    </p>
                </div>
            @else
                <div class="table-responsive">
                    <table id="products-table" class="table table-hover mb-0">
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
                                <th class="text-right" data-orderable="false">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($products as $product)
                                @php $discount = $product->activeDiscount(); @endphp
                                <tr>
                                    <td>
                                        <img class="row-thumb" src="{{ $product->imageUrl(thumb: true) }}"
                                             alt="{{ $product->name }}" loading="lazy">
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.products.show', $product) }}">{{ $product->name }}</a>
                                        <small class="d-block text-muted">{{ $product->unique_id }}</small>
                                    </td>
                                    <td>
                                        {{ $product->category?->name ?? '—' }}
                                        @if ($product->subCategory)
                                            <small class="d-block text-muted">{{ $product->subCategory->name }}</small>
                                        @endif
                                    </td>
                                    <td>{{ $product->unit?->name ?? '—' }}</td>
                                    <td class="text-right">{{ money($product->purchase_price) }}</td>
                                    <td class="text-right">
                                        @if ($discount)
                                            <del class="text-muted">{{ money($product->sale_price) }}</del>
                                            <strong class="d-block">{{ money($product->effective_price) }}</strong>
                                            <span class="badge badge-soft-warning">{{ $discount->label }} off</span>
                                        @else
                                            {{ money($product->sale_price) }}
                                        @endif
                                    </td>
                                    <td class="text-right" data-order="{{ $product->stock_qty }}">
                                        {{ qty($product->stock_qty) }}
                                    </td>
                                    <td>
                                        <span class="badge {{ $product->status ? 'badge-soft-success' : 'badge-soft-muted' }}">
                                            {{ $product->status ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="text-right text-nowrap">
                                        <a href="{{ route('admin.products.show', $product) }}"
                                           class="btn btn-sm btn-secondary" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.products.edit', $product) }}"
                                           class="btn btn-sm btn-secondary" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="{{ route('admin.products.discounts.index', $product) }}"
                                           class="btn btn-sm btn-secondary" title="Discounts">
                                            <i class="fas fa-tags"></i>
                                        </a>
                                        <form action="{{ route('admin.products.destroy', $product) }}" method="post" class="d-inline" data-confirm="Delete this product?" data-confirm-text="This product will be soft deleted." data-confirm-button="Delete">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
@endsection

@push('js')
    <script>
        $(function () {
            // The filter bar above already handles search, so DataTables only
            // provides sorting and paging here.
            $('#products-table').DataTable({
                searching: false,
                order: [],
                columnDefs: [{ orderable: false, targets: 0 }],
            });
        });
    </script>
@endpush
