{{ $product->category?->name ?? '—' }}
@if ($product->subCategory)
    <small class="d-block text-muted">{{ $product->subCategory->name }}</small>
@endif
