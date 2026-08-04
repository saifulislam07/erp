@php
    $badgeColors = [
        'pending' => 'warning', 'processing' => 'info', 'confirmed' => 'primary',
        'on_delivery' => 'secondary', 'delivered' => 'success', 'rejected' => 'danger', 'cancelled' => 'dark',
    ];
@endphp
<span class="badge badge-{{ $badgeColors[$order->status] ?? 'secondary' }}">
    {{ ucfirst(str_replace('_', ' ', $order->status)) }}
</span>
