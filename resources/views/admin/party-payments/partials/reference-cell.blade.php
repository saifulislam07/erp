{{ $payment->payment_id }}
@if ($payment->reference)
    <small class="d-block text-muted">{{ $payment->reference }}</small>
@endif
