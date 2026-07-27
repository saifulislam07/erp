@forelse ($notifications as $notification)
    <a href="{{ route('admin.notifications.index') }}" class="dropdown-item {{ $notification->read_at ? '' : 'font-weight-bold' }}">
        <div class="d-flex justify-content-between align-items-start">
            <span><i class="fas fa-bell mr-2"></i>{{ $notification->data['title'] ?? 'Notification' }}</span>
            <span class="text-muted text-sm ml-2 text-nowrap">{{ $notification->created_at->diffForHumans() }}</span>
        </div>
        <div class="text-muted text-sm">{{ \Illuminate\Support\Str::limit($notification->data['message'] ?? '', 60) }}</div>
    </a>
    <div class="dropdown-divider"></div>
@empty
    <span class="dropdown-item text-muted">No notifications yet.</span>
@endforelse
