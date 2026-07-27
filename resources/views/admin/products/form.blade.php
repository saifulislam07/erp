@php
    $product = $product ?? null;
    $subCategories = $subCategories ?? collect();
    $images = $product?->images ?? collect();
    $selectedSub = (int) old('sub_category_id', $product->sub_category_id ?? 0);
    $currency = trim(\App\Support\Branding::currency());
@endphp

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Product details</h3>
            </div>

            <div class="card-body">
                <div class="form-group">
                    <label for="name">Product name</label>
                    <input type="text" name="name" id="name" required maxlength="255"
                        class="form-control @error('name') is-invalid @enderror"
                        value="{{ old('name', $product->name ?? '') }}"
                        placeholder="e.g. Basmati Rice 5kg">
                    @error('name')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="category_id">Category</label>
                            <select name="category_id" id="category_id" required
                                data-subcategory-url="{{ url('admin/categories') }}"
                                class="form-control @error('category_id') is-invalid @enderror">
                                <option value="">Select a category</option>
                                @foreach ($categories as $cat)
                                    <option value="{{ $cat->id }}"
                                        @selected((int) old('category_id', $product->category_id ?? 0) === $cat->id)>
                                        {{ $cat->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="sub_category_id">Sub-category</label>
                            <select name="sub_category_id" id="sub_category_id"
                                data-selected="{{ $selectedSub ?: '' }}"
                                class="form-control @error('sub_category_id') is-invalid @enderror">
                                <option value="">None</option>
                                @foreach ($subCategories as $sub)
                                    <option value="{{ $sub->id }}" @selected($selectedSub === $sub->id)>
                                        {{ $sub->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('sub_category_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="unit_id">Unit</label>
                            <select name="unit_id" id="unit_id" required
                                class="form-control @error('unit_id') is-invalid @enderror">
                                <option value="">Select a unit</option>
                                @foreach ($units as $unit)
                                    <option value="{{ $unit->id }}"
                                        @selected((int) old('unit_id', $product->unit_id ?? 0) === $unit->id)>
                                        {{ $unit->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('unit_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="form-group mb-0">
                    <label for="description">Description</label>
                    <textarea name="description" id="description" rows="6"
                        class="form-control rich-text @error('description') is-invalid @enderror">{{ old('description', $product->description ?? '') }}</textarea>
                    @error('description')
                        <span class="invalid-feedback d-block">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Pricing</h3>
                <div class="card-tools">
                    <span class="badge badge-soft-muted" id="margin-hint">Margin —</span>
                </div>
            </div>

            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="purchase_price">Purchase price</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">{{ $currency }}</span>
                                </div>
                                <input type="number" step="0.01" min="0" required
                                    name="purchase_price" id="purchase_price"
                                    class="form-control @error('purchase_price') is-invalid @enderror"
                                    value="{{ old('purchase_price', $product->purchase_price ?? '') }}">
                            </div>
                            @error('purchase_price')
                                <span class="invalid-feedback d-block">{{ $message }}</span>
                            @enderror
                            <small class="form-text text-muted">What you pay the supplier.</small>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="sale_price">Sale price</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">{{ $currency }}</span>
                                </div>
                                <input type="number" step="0.01" min="0" required
                                    name="sale_price" id="sale_price"
                                    class="form-control @error('sale_price') is-invalid @enderror"
                                    value="{{ old('sale_price', $product->sale_price ?? '') }}">
                            </div>
                            @error('sale_price')
                                <span class="invalid-feedback d-block">{{ $message }}</span>
                            @enderror
                            <small class="form-text text-muted">What the customer pays.</small>
                        </div>
                    </div>

                    @if (auth()->user()->is_admin)
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="mrp_price">MRP</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">{{ $currency }}</span>
                                    </div>
                                    <input type="number" step="0.01" min="0"
                                        name="mrp_price" id="mrp_price"
                                        class="form-control @error('mrp_price') is-invalid @enderror"
                                        value="{{ old('mrp_price', $product->mrp_price ?? '') }}">
                                </div>
                                @error('mrp_price')
                                    <span class="invalid-feedback d-block">{{ $message }}</span>
                                @enderror
                                <small class="form-text text-muted">Printed ceiling price. Defaults to the sale price.</small>
                            </div>
                        </div>
                    @endif

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="vat_percentage">VAT</label>
                            <div class="input-group">
                                <input type="number" step="0.01" min="0" max="100"
                                    name="vat_percentage" id="vat_percentage"
                                    class="form-control @error('vat_percentage') is-invalid @enderror"
                                    value="{{ old('vat_percentage', $product->vat_percentage ?? 0) }}">
                                <div class="input-group-append">
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>
                            @error('vat_percentage')
                                <span class="invalid-feedback d-block">{{ $message }}</span>
                            @enderror
                            <small class="form-text text-muted" id="vat-hint"></small>
                        </div>
                    </div>
                </div>

                <div class="alert alert-warning d-none mb-0" id="loss-warning">
                    <i class="fas fa-exclamation-triangle mr-1"></i>
                    The sale price is below the purchase price — every sale of this product books a loss.
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Images</h3>
                <div class="card-tools">
                    <span class="badge badge-soft-muted">Up to 8</span>
                </div>
            </div>

            <div class="card-body">
                @if ($images->isNotEmpty())
                    <div class="gallery-grid mb-3">
                        @foreach ($images as $image)
                            <div class="gallery-item {{ $image->is_primary ? 'is-cover' : '' }}">
                                <img src="{{ $image->thumb_url }}" alt="{{ $product->name }}">

                                <label class="gallery-item__pick">
                                    <input type="radio" name="primary_image" value="{{ $image->id }}"
                                        @checked($image->is_primary)>
                                    <span>Cover</span>
                                </label>

                                <label class="gallery-item__drop">
                                    <input type="checkbox" name="remove_images[]" value="{{ $image->id }}">
                                    <span><i class="fas fa-trash"></i></span>
                                </label>
                            </div>
                        @endforeach
                    </div>
                    <small class="form-text text-muted mb-3">
                        Pick which image is the cover; the bin icon removes one when you save.
                    </small>
                @endif

                <div class="form-group mb-0">
                    <label for="images">
                        {{ $images->isNotEmpty() ? 'Add more images' : 'Upload images' }}
                    </label>
                    <input type="file" name="images[]" id="images" multiple
                        accept="image/jpeg,image/png,image/gif,image/webp,image/bmp"
                        class="form-control-file">
                    @error('images')
                        <span class="invalid-feedback d-block">{{ $message }}</span>
                    @enderror
                    @error('images.*')
                        <span class="invalid-feedback d-block">{{ $message }}</span>
                    @enderror
                    <small class="form-text text-muted">
                        JPG, PNG, GIF, BMP or WebP — max 5&nbsp;MB each. Everything is converted to WebP on upload.
                    </small>
                </div>

                <div class="gallery-grid mt-3" id="image-preview"></div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Availability &amp; alerts</h3>
            </div>

            <div class="card-body">
                <div class="custom-control custom-switch mb-3">
                    <input type="hidden" name="status" value="0">
                    <input type="checkbox" name="status" id="status" class="custom-control-input" value="1"
                        @checked(old('status', $product->status ?? true))>
                    <label for="status" class="custom-control-label">
                        Active
                        <small class="d-block text-muted">Inactive products cannot be sold or ordered.</small>
                    </label>
                </div>

                <div class="form-group">
                    <label for="min_stock_threshold">Low-stock alert level</label>
                    <input type="number" step="0.01" min="0" name="min_stock_threshold" id="min_stock_threshold"
                        class="form-control @error('min_stock_threshold') is-invalid @enderror"
                        value="{{ old('min_stock_threshold', $product->min_stock_threshold ?? '') }}"
                        placeholder="Uses the system default when blank">
                    @error('min_stock_threshold')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="custom-control custom-checkbox mb-2">
                    <input type="hidden" name="expire_alert_1month" value="0">
                    <input type="checkbox" name="expire_alert_1month" id="expire_alert_1month"
                        class="custom-control-input" value="1"
                        @checked(old('expire_alert_1month', $product->expire_alert_1month ?? false))>
                    <label for="expire_alert_1month" class="custom-control-label">Alert 1 month before expiry</label>
                </div>

                <div class="custom-control custom-checkbox">
                    <input type="hidden" name="expire_alert_3month" value="0">
                    <input type="checkbox" name="expire_alert_3month" id="expire_alert_3month"
                        class="custom-control-input" value="1"
                        @checked(old('expire_alert_3month', $product->expire_alert_3month ?? false))>
                    <label for="expire_alert_3month" class="custom-control-label">Alert 3 months before expiry</label>
                </div>
            </div>
        </div>
    </div>
</div>

@push('css')
    <link rel="stylesheet" href="{{ asset('assets/plugins/summernote/summernote-bs4.min.css') }}">
@endpush

@push('js')
    <script src="{{ asset('assets/plugins/summernote/summernote-bs4.min.js') }}"></script>
    <script>
        $(function () {
            /*
             * Sub-category loader.
             *
             * `.off` before `.on` matters: this partial is shared by create and
             * edit, and a duplicated binding fired two requests whose responses
             * both appended to the freshly-cleared select — which is why every
             * sub-category appeared twice. The request counter additionally
             * discards a slow response that lands after a newer one.
             */
            const $category = $('#category_id');
            const $sub = $('#sub_category_id');
            let requestId = 0;

            function loadSubCategories(preselect) {
                const categoryId = $category.val();
                const current = ++requestId;

                $sub.prop('disabled', true).html('<option value="">None</option>');

                if (!categoryId) {
                    $sub.prop('disabled', false);
                    return;
                }

                $.getJSON(`${$category.data('subcategory-url')}/${categoryId}/subcategories`)
                    .done(function (items) {
                        if (current !== requestId) {
                            return; // A newer request is already in flight.
                        }

                        $sub.html('<option value="">None</option>');
                        items.forEach(function (item) {
                            $('<option>').val(item.id).text(item.name).appendTo($sub);
                        });

                        if (preselect) {
                            $sub.val(String(preselect));
                        }
                    })
                    .always(function () {
                        if (current === requestId) {
                            $sub.prop('disabled', false);
                        }
                    });
            }

            $category.off('change.subcategories').on('change.subcategories', function () {
                loadSubCategories(null);
            });

            // The server already renders the options for the selected category,
            // so only fetch when the select came back empty.
            if ($category.val() && $sub.find('option').length <= 1) {
                loadSubCategories($sub.data('selected'));
            }

            /* ---------------------------------------------------- editor */
            $('.rich-text').summernote({
                height: 220,
                placeholder: 'Specifications, ingredients, care instructions…',
                toolbar: [
                    ['style', ['bold', 'italic', 'underline', 'clear']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['insert', ['link', 'table']],
                    ['view', ['codeview']],
                ],
            });

            /* --------------------------------------------- pricing hints */
            function refreshPricing() {
                const purchase = parseFloat($('#purchase_price').val()) || 0;
                const sale = parseFloat($('#sale_price').val()) || 0;
                const vat = parseFloat($('#vat_percentage').val()) || 0;

                const margin = sale - purchase;
                const marginPct = sale > 0 ? (margin / sale) * 100 : 0;

                $('#margin-hint')
                    .text(sale > 0 ? `Margin ${margin.toFixed(2)} (${marginPct.toFixed(1)}%)` : 'Margin —')
                    .attr('class', 'badge ' + (
                        sale <= 0 ? 'badge-soft-muted' : margin < 0 ? 'badge-soft-danger' : 'badge-soft-success'
                    ));

                $('#loss-warning').toggleClass('d-none', !(sale > 0 && purchase > 0 && sale < purchase));

                $('#vat-hint').text(
                    vat > 0 ? `Adds ${(sale * vat / 100).toFixed(2)} per unit at checkout.` : 'No VAT on this product.'
                );
            }

            $('#purchase_price, #sale_price, #vat_percentage').on('input', refreshPricing);
            refreshPricing();

            /* ------------------------------------------- upload previews */
            $('#images').on('change', function () {
                const $preview = $('#image-preview').empty();

                Array.from(this.files).slice(0, 8).forEach(function (file) {
                    if (!file.type.startsWith('image/')) {
                        return;
                    }

                    const url = URL.createObjectURL(file);

                    $('<div class="gallery-item gallery-item--new">')
                        .append($('<img>').attr('src', url).on('load', () => URL.revokeObjectURL(url)))
                        .append($('<span class="gallery-item__tag">').text('New'))
                        .appendTo($preview);
                });
            });

            /* Ticking the bin dims the tile so the outcome is obvious. */
            $('input[name="remove_images[]"]').on('change', function () {
                $(this).closest('.gallery-item').toggleClass('is-removed', this.checked);
            });
        });
    </script>
@endpush
