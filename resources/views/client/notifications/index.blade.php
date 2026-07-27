@extends('layouts.client')

@section('title', 'Notifications')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">All Notifications</h3>
        </div>

        <div class="card-body p-0">
            @if ($notifications->isEmpty())
                <p class="p-3 text-muted mb-0">No notifications yet.</p>
            @else
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Notification</th>
                            <th>Received</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($notifications as $notification)
                            <tr class="{{ $notification->read_at ? '' : 'font-weight-bold' }}">
                                <td>
                                    <a href="{{ $notification->data['url'] ?? '#' }}">
                                        {{ $notification->data['title'] ?? 'Notification' }}
                                    </a>
                                    <div class="text-muted text-sm">{{ $notification->data['message'] ?? '' }}</div>
                                </td>
                                <td>{{ $notification->created_at->diffForHumans() }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        @if ($notifications->hasPages())
            <div class="card-footer">
                {{ $notifications->links() }}
            </div>
        @endif
    </div>
@endsection
