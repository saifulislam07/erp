@extends('layouts.admin')

@section('content_title', 'Discounts - ' . $product->name)

@section('content_body')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Discounts for {{ $product->name }}</h3>
            <div class="card-tools">
                <a href="{{ route('admin.products.discounts.create', $product) }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Add Discount
                </a>
                <a href="{{ route('admin.products.index') }}" class="btn btn-secondary btn-sm">Back</a>
            </div>
        </div>

        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Value</th>
                        <th>Start</th>
                        <th>End</th>
                        <th>Applicable To</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($discounts as $discount)
                        <tr>
                            <td>{{ ucfirst($discount->discount_type) }}</td>
                            <td>{{ $discount->discount_value }}{{ $discount->discount_type === 'percentage' ? '%' : '' }}</td>
                            <td>{{ $discount->start_date->format('Y-m-d') }}</td>
                            <td>{{ $discount->end_date?->format('Y-m-d') ?? 'No end date' }}</td>
                            <td>{{ ucfirst(str_replace('_', ' ', $discount->applicable_to)) }}</td>
                            <td>
                                <span class="badge badge-{{ $discount->status ? 'success' : 'danger' }}">
                                    {{ $discount->status ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('admin.products.discounts.edit', [$product, $discount]) }}" class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.products.discounts.destroy', [$product, $discount]) }}" method="post" class="d-inline delete-form">
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
            $('.delete-form').on('submit', function (e) {
                e.preventDefault();
                const form = this;
                Swal.fire({
                    title: 'Are you sure?',
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
