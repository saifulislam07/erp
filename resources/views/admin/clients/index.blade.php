@extends('layouts.admin')

@section('content_title', 'Clients / Agents')

@section('content_body')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">All Clients / Agents</h3>
            <div class="card-tools">
                <a href="{{ route('admin.clients.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Add Client
                </a>
            </div>
        </div>

        <div class="card-body">
            <table id="clients-table" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Unique ID</th>
                        <th>Name</th>
                        <th>Type</th>
                        <th>Phone</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($clients as $client)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $client->unique_id }}</td>
                            <td>{{ $client->name }}</td>
                            <td><span class="badge badge-{{ $client->type === 'agent' ? 'info' : 'secondary' }}">{{ ucfirst($client->type) }}</span></td>
                            <td>{{ $client->phone ?? '-' }}</td>
                            <td>
                                <span class="badge badge-{{ $client->status ? 'success' : 'danger' }}">
                                    {{ $client->status ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('admin.clients.edit', $client) }}" class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.clients.reset-password', $client) }}" method="post" class="d-inline reset-form">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-info">
                                        <i class="fas fa-key"></i>
                                    </button>
                                </form>
                                <form action="{{ route('admin.clients.destroy', $client) }}" method="post"
                                    class="d-inline delete-form">
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
            $('#clients-table').DataTable();

            $('.delete-form').on('submit', function (e) {
                e.preventDefault();
                const form = this;
                Swal.fire({
                    title: 'Are you sure?',
                    text: 'This client will be soft deleted.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, delete it',
                }).then((result) => {
                    if (result.isConfirmed) form.submit();
                });
            });

            $('.reset-form').on('submit', function (e) {
                e.preventDefault();
                const form = this;
                Swal.fire({
                    title: 'Reset password?',
                    text: 'A new random password will be generated and emailed to the client.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, reset it',
                }).then((result) => {
                    if (result.isConfirmed) form.submit();
                });
            });
        });
    </script>
@endpush
