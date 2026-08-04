@if ($payment->allocations->isEmpty())
    <span class="badge badge-soft-info">Advance</span>
@else
    {{ $payment->allocations->count() }} {{ Str::plural('invoice', $payment->allocations->count()) }}
    @if ($payment->unallocated_amount > 0.009)
        <span class="badge badge-soft-info">
            +{{ money($payment->unallocated_amount) }} advance
        </span>
    @endif
@endif
