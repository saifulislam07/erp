@extends('layouts.admin')

@section('content_title', 'Assets')

@section('content_body')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">All Assets</h3>
            <div class="card-tools">
                <a href="{{ route('admin.assets.report') }}" class="btn btn-secondary btn-sm">Report</a>
                <a href="{{ route('admin.assets.create') }}" class="btn btn-primary btn-sm">Add Asset</a>
            </div>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.assets.index') }}" method="get" class="form-inline mb-3">
                <input type="text" name="q" class="form-control mr-2" placeholder="Search ID/name/serial..." value="{{ request('q') }}">
                <select name="status" class="form-control mr-2">
                    <option value="">-- All Statuses --</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="disposed" {{ request('status') === 'disposed' ? 'selected' : '' }}>Disposed</option>
                    <option value="lost" {{ request('status') === 'lost' ? 'selected' : '' }}>Lost</option>
                </select>
                <button type="submit" class="btn btn-secondary">Filter</button>
            </form>

            <table id="assets-table" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Asset ID</th>
                        <th>Name</th>
                        <th>Serial Number</th>
                        <th>Category</th>
                        <th>Purchase Price</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($assets as $asset)
                        <tr>
                            <td>{{ $asset->asset_id }}</td>
                            <td>{{ $asset->name }}</td>
                            <td>{{ $asset->serial_number ?? '-' }}</td>
                            <td>{{ $asset->category ?? '-' }}</td>
                            <td>{{ $asset->purchase_price }}</td>
                            <td>
                                <span class="badge badge-{{ ['active' => 'success', 'disposed' => 'secondary', 'lost' => 'danger'][$asset->status] }}">
                                    {{ ucfirst($asset->status) }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('admin.assets.show', $asset) }}" class="btn btn-sm btn-info">View</a>
                                <a href="{{ route('admin.assets.edit', $asset) }}" class="btn btn-sm btn-warning">Edit</a>
                                <form action="{{ route('admin.assets.destroy', $asset) }}" method="post" class="d-inline" data-confirm="Delete this asset?" data-confirm-button="Delete">
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

@push('js')
    <script>
        $(function () {
            $('#assets-table').DataTable();

        });
    </script>
@endpush
