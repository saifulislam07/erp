@extends('layouts.admin')

@section('content_title', 'Suppliers')

@section('content_body')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">All Suppliers</h3>
            <div class="card-tools">
                <a href="{{ route('admin.suppliers.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Add Supplier
                </a>
            </div>
        </div>

        <div class="card-body">
            <table id="suppliers-table" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Phone</th>
                        <th>Company</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($suppliers as $supplier)
                        <tr>
                            <td>{{ $supplier->unique_id }}</td>
                            <td>{{ $supplier->name }}</td>
                            <td>{{ $supplier->phone }}</td>
                            <td>{{ $supplier->company_name ?? '-' }}</td>
                            <td>
                                <span class="badge badge-{{ $supplier->status ? 'success' : 'danger' }}">
                                    {{ $supplier->status ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('admin.suppliers.edit', $supplier) }}" class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.suppliers.destroy', $supplier) }}" method="post" class="d-inline" data-confirm="Delete this supplier?" data-confirm-button="Delete">
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
            $('#suppliers-table').DataTable();

        });
    </script>
@endpush
