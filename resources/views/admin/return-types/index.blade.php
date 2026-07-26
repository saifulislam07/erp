@extends('layouts.admin')

@section('content_title', 'Return Types')

@section('content_body')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Return Types</h3>
            <div class="card-tools">
                <a href="{{ route('admin.return-types.create') }}" class="btn btn-primary btn-sm">Add Return Type</a>
            </div>
        </div>
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Disposition</th>
                        <th>Description</th>
                        <th>Used</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($returnTypes as $type)
                        <tr>
                            <td>{{ $type->name }}</td>
                            <td>
                                <span class="badge badge-{{ $type->disposition === 'restock' ? 'success' : 'danger' }}">
                                    {{ $type->disposition === 'restock' ? 'Restock' : 'Damage Section' }}
                                </span>
                            </td>
                            <td>{{ $type->description }}</td>
                            <td>{{ $type->returns_count }}</td>
                            <td>
                                <a href="{{ route('admin.return-types.edit', $type) }}" class="btn btn-sm btn-warning">Edit</a>
                                <form action="{{ route('admin.return-types.destroy', $type) }}" method="post" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
