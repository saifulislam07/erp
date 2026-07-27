@extends('layouts.admin')

@section('content_title', 'Roles')

@section('content_body')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">All Roles</h3>
            @can('role.create')
                <div class="card-tools">
                    <a href="{{ route('admin.roles.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Add Role
                    </a>
                </div>
            @endcan
        </div>

        <div class="card-body">
            <table id="roles-table" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Role Name</th>
                        <th>Permissions Count</th>
                        <th>Users</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($roles as $role)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $role->name }}</td>
                            <td>{{ $role->permissions_count }}</td>
                            <td>{{ $role->users_count }}</td>
                            <td>{{ $role->created_at->format('Y-m-d') }}</td>
                            <td>
                                @can('role.edit')
                                    <a href="{{ route('admin.roles.edit', $role) }}" class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                @endcan
                                @can('role.delete')
                                    <form action="{{ route('admin.roles.destroy', $role) }}" method="post"
                                        class="d-inline delete-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                @endcan
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
            $('#roles-table').DataTable();

            $('.delete-form').on('submit', function (e) {
                e.preventDefault();
                const form = this;

                Swal.fire({
                    title: 'Are you sure?',
                    text: 'This role will be permanently deleted.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, delete it',
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });
    </script>
@endpush
