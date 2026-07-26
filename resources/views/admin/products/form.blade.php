@php $product = $product ?? null; @endphp

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="category_id">Category</label>
            <select name="category_id" id="category_id" class="form-control @error('category_id') is-invalid @enderror">
                <option value="">-- Select Category --</option>
                @foreach ($categories as $cat)
                    <option value="{{ $cat->id }}"
                        {{ (int) old('category_id', $product->category_id ?? '') === $cat->id ? 'selected' : '' }}>
                        {{ $cat->name }}
                    </option>
                @endforeach
            </select>
            @error('category_id')
                <span class="invalid-feedback">{{ $message }}</span>
            @enderror
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-group">
            <label for="sub_category_id">Sub-Category</label>
            <select name="sub_category_id" id="sub_category_id" class="form-control @error('sub_category_id') is-invalid @enderror">
                <option value="">-- None --</option>
                @isset($subCategories)
                    @foreach ($subCategories as $sub)
                        <option value="{{ $sub->id }}"
                            {{ (int) old('sub_category_id', $product->sub_category_id ?? '') === $sub->id ? 'selected' : '' }}>
                            {{ $sub->name }}
                        </option>
                    @endforeach
                @endisset
            </select>
            @error('sub_category_id')
                <span class="invalid-feedback">{{ $message }}</span>
            @enderror
        </div>
    </div>
</div>

<div class="form-group">
    <label for="name">Name</label>
    <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror"
        value="{{ old('name', $product->name ?? '') }}">
    @error('name')
        <span class="invalid-feedback">{{ $message }}</span>
    @enderror
</div>

<div class="form-group">
    <label for="description">Description</label>
    <textarea name="description" id="description" rows="3"
        class="form-control @error('description') is-invalid @enderror">{{ old('description', $product->description ?? '') }}</textarea>
    @error('description')
        <span class="invalid-feedback">{{ $message }}</span>
    @enderror
</div>

<div class="form-group">
    <label for="unit_id">Unit</label>
    <select name="unit_id" id="unit_id" class="form-control @error('unit_id') is-invalid @enderror">
        <option value="">-- Select Unit --</option>
        @foreach ($units as $unit)
            <option value="{{ $unit->id }}" {{ (int) old('unit_id', $product->unit_id ?? '') === $unit->id ? 'selected' : '' }}>
                {{ $unit->name }} ({{ $unit->symbol }})
            </option>
        @endforeach
    </select>
    @error('unit_id')
        <span class="invalid-feedback">{{ $message }}</span>
    @enderror
</div>

<div class="row">
    @if (auth()->user()->is_admin)
        <div class="col-md-3">
            <div class="form-group">
                <label for="mrp_price">MRP Price</label>
                <input type="number" step="0.01" name="mrp_price" id="mrp_price"
                    class="form-control @error('mrp_price') is-invalid @enderror"
                    value="{{ old('mrp_price', $product->mrp_price ?? '') }}">
                @error('mrp_price')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>
        </div>
    @endif

    <div class="col-md-3">
        <div class="form-group">
            <label for="purchase_price">Purchase Price</label>
            <input type="number" step="0.01" name="purchase_price" id="purchase_price"
                class="form-control @error('purchase_price') is-invalid @enderror"
                value="{{ old('purchase_price', $product->purchase_price ?? '') }}">
            @error('purchase_price')
                <span class="invalid-feedback">{{ $message }}</span>
            @enderror
        </div>
    </div>

    <div class="col-md-3">
        <div class="form-group">
            <label for="sale_price">Sale Price</label>
            <input type="number" step="0.01" name="sale_price" id="sale_price"
                class="form-control @error('sale_price') is-invalid @enderror"
                value="{{ old('sale_price', $product->sale_price ?? '') }}">
            @error('sale_price')
                <span class="invalid-feedback">{{ $message }}</span>
            @enderror
        </div>
    </div>

    <div class="col-md-3">
        <div class="form-group">
            <label for="vat_percentage">VAT %</label>
            <input type="number" step="0.01" name="vat_percentage" id="vat_percentage"
                class="form-control @error('vat_percentage') is-invalid @enderror"
                value="{{ old('vat_percentage', $product->vat_percentage ?? 0) }}">
            <small id="vat-display" class="form-text text-muted"></small>
            @error('vat_percentage')
                <span class="invalid-feedback">{{ $message }}</span>
            @enderror
        </div>
    </div>
</div>

<div class="form-group">
    <label for="image">Image</label>
    <input type="file" name="image" id="image" class="form-control-file @error('image') is-invalid @enderror">
    @error('image')
        <span class="invalid-feedback d-block">{{ $message }}</span>
    @enderror
    @if (!empty($product?->image))
        <img src="{{ asset('storage/'.$product->image) }}" alt="{{ $product->name }}" class="mt-2" style="max-height: 80px;">
    @endif
</div>

<div class="form-group form-check">
    <input type="hidden" name="status" value="0">
    <input type="checkbox" name="status" id="status" class="form-check-input" value="1"
        {{ old('status', $product->status ?? true) ? 'checked' : '' }}>
    <label for="status" class="form-check-label">Active</label>
</div>

<div class="form-group form-check">
    <input type="hidden" name="expire_alert_1month" value="0">
    <input type="checkbox" name="expire_alert_1month" id="expire_alert_1month" class="form-check-input" value="1"
        {{ old('expire_alert_1month', $product->expire_alert_1month ?? false) ? 'checked' : '' }}>
    <label for="expire_alert_1month" class="form-check-label">Alert when expiring within 1 month</label>
</div>

<div class="form-group form-check">
    <input type="hidden" name="expire_alert_3month" value="0">
    <input type="checkbox" name="expire_alert_3month" id="expire_alert_3month" class="form-check-input" value="1"
        {{ old('expire_alert_3month', $product->expire_alert_3month ?? false) ? 'checked' : '' }}>
    <label for="expire_alert_3month" class="form-check-label">Alert when expiring within 3 months</label>
</div>

@push('js')
    <script>
        $(function () {
            function updateVatDisplay() {
                const vat = parseFloat($('#vat_percentage').val()) || 0;
                $('#vat-display').text(vat > 0 ? `VAT applies: ${vat}%` : 'No VAT');
            }

            updateVatDisplay();
            $('#vat_percentage').on('input', updateVatDisplay);

            $('#category_id').on('change', function () {
                const categoryId = $(this).val();
                const $sub = $('#sub_category_id');
                $sub.html('<option value="">-- None --</option>');

                if (!categoryId) return;

                $.get(`/admin/categories/${categoryId}/subcategories`, function (data) {
                    data.forEach((item) => {
                        $sub.append(`<option value="${item.id}">${item.name}</option>`);
                    });
                });
            });
        });
    </script>
@endpush
