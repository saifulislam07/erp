<span class="badge badge-{{ $purchase->payment_status === 'paid' ? 'success' : ($purchase->payment_status === 'partial' ? 'warning' : 'danger') }}">
    {{ ucfirst($purchase->payment_status) }}
</span>
