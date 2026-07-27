@extends('layouts.admin')

@section('content_title', $product->name)

@section('content_body')
    @php
        $activeDiscount = $product->activeDiscount();
        $effective = $product->effective_price;
        $vatAmount = $effective * (float) $product->vat_percentage / 100;
        $threshold = $product->min_stock_threshold ?? (float) \App\Models\Setting::get('low_stock_threshold_default', 0);
        $isLow = $threshold > 0 && $stock <= $threshold;
    @endphp

    <div class="card">
        <div class="card-body page-actions">
            <a href="{{ route('admin.products.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left mr-1"></i> All products
            </a>
            <span class="badge badge-soft-muted">{{ $product->unique_id }}</span>
            <span class="badge {{ $product->status ? 'badge-soft-success' : 'badge-soft-danger' }}">
                {{ $product->status ? 'Active' : 'Inactive' }}
            </span>
            @if ($activeDiscount)
                <span class="badge badge-soft-warning">{{ $activeDiscount->label }} off now</span>
            @endif

            <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-primary btn-sm ml-auto">
                <i class="fas fa-edit mr-1"></i> Edit
            </a>
            <a href="{{ route('admin.products.discounts.index', $product) }}" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-tags mr-1"></i> Discounts
            </a>
        </div>
    </div>

    <div class="row">
        {{-- ------------------------------------------------------ gallery --}}
        <div class="col-lg-4">
            <div class="card">
                <div class="card-body">
                    @php $gallery = $product->images; @endphp

                    <img id="gallery-main" class="gallery-viewer__main"
                         src="{{ $gallery->first()?->url ?? $product->imageUrl() }}"
                         alt="{{ $product->name }}">

                    @if ($gallery->count() > 1)
                        <div class="gallery-viewer__thumbs">
                            @foreach ($gallery as $image)
                                <button type="button"
                                        class="gallery-viewer__thumb {{ $loop->first ? 'is-active' : '' }}"
                                        data-full="{{ $image->url }}">
                                    <img src="{{ $image->thumb_url }}" alt="">
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Stock on hand</h3>
                    <div class="card-tools">
                        <span class="badge {{ $isLow ? 'badge-soft-danger' : 'badge-soft-success' }}">
                            {{ qty($stock) }} {{ $product->unit?->name }}
                        </span>
                    </div>
                </div>

                <div class="card-body p-0">
                    @if ($stockByStore->isEmpty())
                        <div class="empty-state">
                            <i class="fas fa-boxes"></i>
                            <p>Nothing in stock yet.</p>
                        </div>
                    @else
                        <table class="table table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>Store</th>
                                    <th class="text-right">Quantity</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($stockByStore as $row)
                                    <tr>
                                        <td>{{ $row->store_name ?? 'Unassigned' }}</td>
                                        <td class="text-right">{{ qty($row->quantity) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td>Total</td>
                                    <td class="text-right">{{ qty($stock) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    @endif
                </div>

                @if ($isLow)
                    <div class="card-footer">
                        <span class="text-danger">
                            <i class="fas fa-exclamation-triangle mr-1"></i>
                            At or below the alert level of {{ qty($threshold) }}.
                        </span>
                    </div>
                @endif
            </div>
        </div>

        {{-- -------------------------------------------------- information --}}
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Pricing</h3>
                </div>

                <div class="card-body">
                    <div class="row">
                        <div class="col-6 col-md-3">
                            <div class="stat-tile stat-tile--muted">
                                <span class="stat-tile__icon"><i class="fas fa-tag"></i></span>
                                <span class="stat-tile__body">
                                    <span class="stat-tile__label">Purchase</span>
                                    <span class="stat-tile__value">{{ money($product->purchase_price) }}</span>
                                </span>
                            </div>
                        </div>

                        <div class="col-6 col-md-3">
                            <div class="stat-tile stat-tile--primary">
                                <span class="stat-tile__icon"><i class="fas fa-hand-holding-usd"></i></span>
                                <span class="stat-tile__body">
                                    <span class="stat-tile__label">Sale price</span>
                                    <span class="stat-tile__value">{{ money($product->sale_price) }}</span>
                                </span>
                            </div>
                        </div>

                        <div class="col-6 col-md-3">
                            <div class="stat-tile {{ $activeDiscount ? 'stat-tile--warning' : 'stat-tile--muted' }}">
                                <span class="stat-tile__icon"><i class="fas fa-percent"></i></span>
                                <span class="stat-tile__body">
                                    <span class="stat-tile__label">Selling now</span>
                                    <span class="stat-tile__value">{{ money($effective) }}</span>
                                    @if ($activeDiscount)
                                        <span class="stat-tile__meta">−{{ money($product->discount_amount) }} discount</span>
                                    @endif
                                </span>
                            </div>
                        </div>

                        <div class="col-6 col-md-3">
                            <div class="stat-tile {{ $product->margin < 0 ? 'stat-tile--danger' : 'stat-tile--success' }}">
                                <span class="stat-tile__icon"><i class="fas fa-chart-line"></i></span>
                                <span class="stat-tile__body">
                                    <span class="stat-tile__label">Margin / unit</span>
                                    <span class="stat-tile__value">{{ money($product->margin) }}</span>
                                    <span class="stat-tile__meta">{{ percent($product->margin_percentage, 1) }} of price</span>
                                </span>
                            </div>
                        </div>
                    </div>

                    <table class="table table-sm mb-0">
                        <tbody>
                            <tr>
                                <th style="width: 45%">MRP (printed ceiling)</th>
                                <td>{{ money($product->mrp_price) }}</td>
                            </tr>
                            <tr>
                                <th>VAT</th>
                                <td>
                                    @if ($product->vat_percentage > 0)
                                        {{ percent($product->vat_percentage) }}
                                        <span class="text-muted">— {{ money($vatAmount) }} per unit</span>
                                    @else
                                        <span class="text-muted">Not applicable</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Price including VAT</th>
                                <td><strong>{{ money($effective + $vatAmount) }}</strong></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Details</h3>
                </div>

                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <dl class="detail-list">
                                <dt>Category</dt>
                                <dd>{{ $product->category?->name ?? '—' }}</dd>
                                <dt>Sub-category</dt>
                                <dd>{{ $product->subCategory?->name ?? '—' }}</dd>
                            </dl>
                        </div>
                        <div class="col-md-4">
                            <dl class="detail-list">
                                <dt>Unit</dt>
                                <dd>{{ $product->unit?->name ?? '—' }}</dd>
                                <dt>Low-stock alert at</dt>
                                <dd>{{ $threshold > 0 ? qty($threshold) : 'Not set' }}</dd>
                            </dl>
                        </div>
                        <div class="col-md-4">
                            <dl class="detail-list">
                                <dt>Expiry alerts</dt>
                                <dd>
                                    @if ($product->expire_alert_1month || $product->expire_alert_3month)
                                        {{ collect([
                                            $product->expire_alert_1month ? '1 month' : null,
                                            $product->expire_alert_3month ? '3 months' : null,
                                        ])->filter()->implode(', ') }}
                                    @else
                                        None
                                    @endif
                                </dd>
                                <dt>Added</dt>
                                <dd>{{ $product->created_at?->format('d M Y') }}</dd>
                            </dl>
                        </div>
                    </div>

                    @if (filled($product->description))
                        <hr>
                        <div class="rich-content">{!! $product->description !!}</div>
                    @endif
                </div>
            </div>

            {{-- ------------------------------------------------- discounts --}}
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Discounts</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.products.discounts.create', $product) }}" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-plus mr-1"></i> Add
                        </a>
                    </div>
                </div>

                <div class="card-body p-0">
                    @if ($product->discounts->isEmpty())
                        <div class="empty-state">
                            <i class="fas fa-tags"></i>
                            <p>No discounts configured for this product.</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-sm mb-0">
                                <thead>
                                    <tr>
                                        <th>Amount</th>
                                        <th>Audience</th>
                                        <th>Period</th>
                                        <th class="text-right">Price while running</th>
                                        <th>State</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($product->discounts as $discount)
                                        <tr>
                                            <td><strong>{{ $discount->label }}</strong></td>
                                            <td>
                                                {{ [
                                                    'all' => 'Everyone',
                                                    'client_agent' => 'Clients & agents',
                                                    'local' => 'Local buyers',
                                                ][$discount->applicable_to] ?? $discount->applicable_to }}
                                            </td>
                                            <td>{{ $discount->period }}</td>
                                            <td class="text-right">{{ money($discount->applyTo((float) $product->sale_price)) }}</td>
                                            <td>
                                                @if (! $discount->status)
                                                    <span class="badge badge-soft-muted">Disabled</span>
                                                @elseif ($discount->isActive())
                                                    <span class="badge badge-soft-success">Running</span>
                                                @elseif ($discount->start_date?->isFuture())
                                                    <span class="badge badge-soft-info">Scheduled</span>
                                                @else
                                                    <span class="badge badge-soft-muted">Ended</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

            {{-- --------------------------------------------------- history --}}
            <div class="card">
                <div class="card-header p-0 border-bottom-0">
                    <ul class="nav nav-tabs w-100 px-2 pt-2" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" href="#purchase-history" data-toggle="tab">Recent purchases</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#sales-history" data-toggle="tab">Recent sales</a>
                        </li>
                    </ul>
                </div>

                <div class="card-body p-0">
                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="purchase-history">
                            @if ($purchaseHistory->isEmpty())
                                <div class="empty-state">
                                    <i class="fas fa-truck-loading"></i>
                                    <p>This product has not been purchased yet.</p>
                                </div>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-sm mb-0">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Invoice</th>
                                                <th>Supplier</th>
                                                <th class="text-right">Qty</th>
                                                <th class="text-right">Unit price</th>
                                                <th class="text-right">Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($purchaseHistory as $row)
                                                <tr>
                                                    <td>{{ \Illuminate\Support\Carbon::parse($row->dated_on)->format('d M Y') }}</td>
                                                    <td>
                                                        <a href="{{ route('admin.purchases.show', $row->id) }}">{{ $row->reference }}</a>
                                                    </td>
                                                    <td>{{ $row->party_name ?? '—' }}</td>
                                                    <td class="text-right">{{ qty($row->quantity) }}</td>
                                                    <td class="text-right">{{ money($row->unit_price) }}</td>
                                                    <td class="text-right">{{ money($row->total_price) }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>

                        <div class="tab-pane fade" id="sales-history">
                            @if ($salesHistory->isEmpty())
                                <div class="empty-state">
                                    <i class="fas fa-cash-register"></i>
                                    <p>This product has not been sold yet.</p>
                                </div>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-sm mb-0">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Invoice</th>
                                                <th>Customer</th>
                                                <th class="text-right">Qty</th>
                                                <th class="text-right">Unit price</th>
                                                <th class="text-right">Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($salesHistory as $row)
                                                <tr>
                                                    <td>{{ \Illuminate\Support\Carbon::parse($row->dated_on)->format('d M Y') }}</td>
                                                    <td>
                                                        <a href="{{ route('admin.sales.show', $row->id) }}">{{ $row->reference }}</a>
                                                    </td>
                                                    <td>{{ $row->party_name ?? 'Walk-in' }}</td>
                                                    <td class="text-right">{{ qty($row->quantity) }}</td>
                                                    <td class="text-right">{{ money($row->unit_price) }}</td>
                                                    <td class="text-right">{{ money($row->total_price) }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script>
        $(function () {
            $('.gallery-viewer__thumb').on('click', function () {
                $('#gallery-main').attr('src', $(this).data('full'));
                $('.gallery-viewer__thumb').removeClass('is-active');
                $(this).addClass('is-active');
            });
        });
    </script>
@endpush
