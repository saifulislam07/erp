@extends('layouts.admin')

@section('content_title', 'Products')

@section('content_body')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">All Products</h3>
            <div class="card-tools">
                <a href="{{ route('admin.products.report') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-chart-bar"></i> Report
                </a>
                <a href="{{ route('admin.products.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Add Product
                </a>
            </div>
        </div>

        <div class="card-body">
            <table id="products-table" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Unit</th>
                        <th>MRP</th>
                        <th>Sale Price</th>
                        <th>Stock Qty</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($products as $product)
                        <tr>
                            <td>{{ $product->unique_id }}</td>
                            <td>{{ $product->name }}</td>
                            <td>{{ $product->category?->name }}</td>
                            <td>{{ $product->unit?->symbol }}</td>
                            <td>{{ $product->mrp_price }}</td>
                            <td>{{ $product->sale_price }}</td>
                            <td>{{ $product->stock_qty }}</td>
                            <td>
                                <span class="badge badge-{{ $product->status ? 'success' : 'danger' }}">
                                    {{ $product->status ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('admin.products.show', $product) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="{{ route('admin.products.discounts.index', $product) }}" class="btn btn-sm btn-secondary">
                                    <i class="fas fa-percent"></i>
                                </a>
                                <form action="{{ route('admin.products.destroy', $product) }}" method="post" class="d-inline delete-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('js')
    <script>
        $(function () {
            $('#products-table').DataTable();

            $('.delete-form').on('submit', function (e) {
                e.preventDefault();
                const form = this;
                Swal.fire({
                    title: 'Are you sure?',
                    text: 'This product will be soft deleted.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, delete it',
                }).then((result) => {
                    if (result.isConfirmed) form.submit();
                });
            });
        });
    </script>
@endpush
