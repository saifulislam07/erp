@extends('layouts.admin')

@section('content_title', 'Returns for ' . $purchase->purchase_id)

@section('content_body')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Returns for Purchase {{ $purchase->purchase_id }}</h3>
            <div class="card-tools">
                <a href="{{ route('admin.purchases.returns.create', $purchase) }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> New Return
                </a>
                <a href="{{ route('admin.purchases.show', $purchase) }}" class="btn btn-secondary btn-sm">Back</a>
            </div>
        </div>

        <div class="card-body">
            @if ($returns->isEmpty())
                <p class="text-muted mb-0">No returns recorded for this purchase.</p>
            @else
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Return ID</th>
                            <th>Date</th>
                            <th>Reason</th>
                            <th>Items</th>
                            <th>Total</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($returns as $return)
                            <tr>
                                <td>{{ $return->return_id }}</td>
                                <td>{{ $return->return_date->format('Y-m-d') }}</td>
                                <td>{{ $return->reason }}</td>
                                <td>{{ $return->items->pluck('product.name')->join(', ') }}</td>
                                <td>{{ $return->total_amount }}</td>
                                <td>
                                    <a href="{{ route('admin.purchases.returns.edit', [$purchase, $return]) }}" class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.purchases.returns.destroy', [$purchase, $return]) }}" method="post" class="d-inline delete-form">
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
            @endif
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
                    text: 'This will restore the returned stock quantities.',
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
