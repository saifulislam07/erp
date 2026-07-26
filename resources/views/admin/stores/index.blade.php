@extends('layouts.admin')

@section('content_title', 'Stores')

@section('content_body')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">All Stores</h3>
            <div class="card-tools">
                <a href="{{ route('admin.stores.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Add Store
                </a>
            </div>
        </div>

        <div class="card-body">
            <table id="stores-table" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Location</th>
                        <th>Stock Entries</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($stores as $store)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $store->name }}</td>
                            <td>{{ $store->location ?? '-' }}</td>
                            <td>{{ $store->stocks_count }}</td>
                            <td>
                                <span class="badge badge-{{ $store->status ? 'success' : 'danger' }}">
                                    {{ $store->status ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('admin.stores.edit', $store) }}" class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.stores.destroy', $store) }}" method="post" class="d-inline delete-form">
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
            $('#stores-table').DataTable();

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
