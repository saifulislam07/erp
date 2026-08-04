@extends('layouts.admin')

@section('content_title', 'Clients / Agents')

@section('content_body')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title" id="clients-count">All Clients / Agents</h3>
            <div class="card-tools">
                <a href="{{ route('admin.clients.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Add Client
                </a>
            </div>
        </div>

        <div class="card-body">
            <table id="clients-table" class="table table-bordered table-striped" style="width: 100%">
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
            </table>
        </div>
    </div>
@endsection

@push('js')
    <script>
        $(function () {
            ERP.serverTable('#clients-table', {
                url: '{{ route('admin.clients.index') }}',
                count: '#clients-count',
                noun: 'client / agent',
                empty: 'No clients or agents yet.',
                order: [[1, 'desc']],
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'unique_id', name: 'unique_id' },
                    { data: 'name', name: 'name' },
                    { data: 'type_badge', name: 'type_badge' },
                    { data: 'phone_number', name: 'phone_number' },
                    { data: 'state', name: 'state' },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-nowrap' },
                ],
            });
        });
    </script>
@endpush
