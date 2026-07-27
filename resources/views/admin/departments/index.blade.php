@extends('layouts.admin')

@section('content_title', 'Departments')

@section('content_body')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">All Departments</h3>
            <div class="card-tools">
                <a href="{{ route('admin.departments.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Add Department
                </a>
            </div>
        </div>

        <div class="card-body">
            <table id="departments-table" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Users</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($departments as $department)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $department->name }}</td>
                            <td>{{ $department->description ?? '-' }}</td>
                            <td>{{ $department->users_count }}</td>
                            <td>{{ $department->created_at->format('Y-m-d') }}</td>
                            <td>
                                <a href="{{ route('admin.departments.edit', $department) }}" class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.departments.destroy', $department) }}" method="post"
                                    class="d-inline" data-confirm="Delete this department?" data-confirm-text="This department will be soft deleted." data-confirm-button="Delete">
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
            $('#departments-table').DataTable();

        });
    </script>
@endpush
