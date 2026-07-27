@extends('layouts.admin')

@section('content_title', 'Activity Log')

@section('content_body')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Filters</h3>
        </div>
        <div class="card-body">
            <form method="get" class="form-inline">
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
                <a href="{{ route('admin.activity-log.index') }}" class="btn btn-secondary mb-2">Reset</a>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <table id="activity-log-table" class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>User</th>
                        <th>Event</th>
                        <th>Module</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($activities as $activity)
                        <tr>
                            <td>{{ $activity->created_at->format('Y-m-d H:i') }}</td>
                            <td>{{ $activity->causer?->name ?? 'System' }}</td>
                            <td><span class="badge badge-secondary">{{ ucfirst($activity->event ?? '') }}</span></td>
                            <td>{{ class_basename($activity->subject_type) }} #{{ $activity->subject_id }}</td>
                            <td>{{ $activity->description }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="card-footer">
            {{ $activities->links() }}
        </div>
    </div>
@endsection

@push('js')
    <script>
        $(function () {
            $('#activity-log-table').DataTable({ paging: false, searching: false, info: false, ordering: false });
        });
    </script>
@endpush
