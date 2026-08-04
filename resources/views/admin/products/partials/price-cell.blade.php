@php $discount = $product->activeDiscount(); @endphp
@if ($discount)
    <del class="text-muted">{{ money($product->sale_price) }}</del>
    <strong class="d-block">{{ money($product->effective_price) }}</strong>
    <span class="badge badge-soft-warning">{{ $discount->label }} off</span>
@else
    {{ money($product->sale_price) }}
@endif
