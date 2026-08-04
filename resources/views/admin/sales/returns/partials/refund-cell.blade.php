@if ((float) $return->refund_amount > 0)
    {{ money($return->refund_amount) }}
    <small class="d-block text-muted">
        {{ ucwords(str_replace('_', ' ', $return->refund_method)) }}
    </small>
@else
    <span class="text-muted">Credited</span>
@endif
