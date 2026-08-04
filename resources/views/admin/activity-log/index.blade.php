@extends('layouts.admin')

@section('content_title', 'Activity Log')

@section('content_body')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Filters</h3>
        </div>
        <div class="card-body">
            <form method="get" class="form-inline" id="activity-log-filter" data-no-submit-guard>
                <select name="subject_type" class="form-control mr-2 mb-2">
                    <option value="">All Modules</option>
                    @foreach ($models as $label => $class)
                        <option value="{{ $class }}" @selected(request('subject_type') === $class)>{{ $label }}</option>
                    @endforeach
                </select>

                <select name="causer_id" class="form-control mr-2 mb-2">
                    <option value="">All Users</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}" @selected((string) request('causer_id') === (string) $user->id)>{{ $user->name }}</option>
                    @endforeach
                </select>

                <input type="date" name="from_date" value="{{ request('from_date') }}" class="form-control mr-2 mb-2" placeholder="From">
                <input type="date" name="to_date" value="{{ request('to_date') }}" class="form-control mr-2 mb-2" placeholder="To">

                <button type="submit" class="btn btn-primary mb-2 mr-2">Filter</button>
                <a href="#" class="btn btn-secondary mb-2" data-table-clear>Reset</a>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title" id="activity-log-count">Activity</h3>
        </div>
        <div class="card-body p-0">
            <table id="activity-log-table" class="table table-hover mb-0" style="width: 100%">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>User</th>
                        <th>Event</th>
                        <th>Module</th>
                        <th>Description</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
@endsection

@push('js')
    <script>
        $(function () {
            ERP.serverTable('#activity-log-table', {
                url: '{{ route('admin.activity-log.index') }}',
                filter: '#activity-log-filter',
                count: '#activity-log-count',
                noun: 'entry',
                nounPlural: 'entries',
                empty: 'Nothing logged yet.',
                order: [[0, 'desc']],
                options: { pageLength: 50 },
                columns: [
                    { data: 'logged_at', name: 'logged_at', searchable: false },
                    { data: 'causer_name', name: 'causer_name' },
                    { data: 'event_badge', name: 'event_badge' },
                    { data: 'module', name: 'module' },
                    { data: 'description', name: 'description' },
                ],
            });
        });
    </script>
@endpush
