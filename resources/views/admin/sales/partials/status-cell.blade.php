<span class="badge badge-{{ $sale->payment_status === 'paid' ? 'success' : ($sale->payment_status === 'partial' ? 'warning' : 'danger') }}">
    {{ ucfirst($sale->payment_status) }}
</span>
