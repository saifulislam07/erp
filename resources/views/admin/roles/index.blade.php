@extends('layouts.admin')

@section('content_title', 'Roles')

@section('content_body')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">All Roles</h3>
        </div>

        <div class="card-body">
            <table id="roles-table" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Role Name</th>
                        <th>Permissions Count</th>
                        <th>Created At</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($roles as $role)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $role->name }}</td>
                            <td>{{ $role->permissions_count }}</td>
                            <td>{{ $role->created_at->format('Y-m-d') }}</td>
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
        });
    </script>
@endpush
