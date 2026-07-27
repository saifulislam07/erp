@extends('layouts.admin')

@section('content_title', 'Dashboard')

@section('content_body')
    {{--
        Tiles are grouped by the question they answer — what happened today,
        where the money stands, what the catalogue looks like — instead of one
        undifferentiated wall of large boxes.
    --}}

    @if ($can['sale'] || $can['purchase'] || $can['expense'] || $can['order'])
        <p class="section-label">Today &middot; {{ now()->format('d M Y') }}</p>

        <div class="row">
            @if ($can['sale'])
                <div class="col-6 col-md-4 col-xl-3">
                    <a class="stat-tile stat-tile--success" href="{{ route('admin.sales.index') }}">
                        <span class="stat-tile__icon"><i class="fas fa-cash-register"></i></span>
                        <span class="stat-tile__body">
                            <span class="stat-tile__label">Sales today</span>
                            <span class="stat-tile__value">{{ money($today['sales_total']) }}</span>
                            <span class="stat-tile__meta">{{ $today['sales_count'] }} {{ Str::plural('invoice', $today['sales_count']) }}</span>
                        </span>
                    </a>
                </div>
            @endif

            @if ($can['purchase'])
                <div class="col-6 col-md-4 col-xl-3">
                    <a class="stat-tile stat-tile--warning" href="{{ route('admin.purchases.index') }}">
                        <span class="stat-tile__icon"><i class="fas fa-truck-loading"></i></span>
                        <span class="stat-tile__body">
                            <span class="stat-tile__label">Purchases today</span>
                            <span class="stat-tile__value">{{ money($today['purchases_total']) }}</span>
                        </span>
                    </a>
                </div>
            @endif

            @if ($can['expense'])
                <div class="col-6 col-md-4 col-xl-3">
                    <a class="stat-tile stat-tile--danger" href="{{ route('admin.expenses.index') }}">
                        <span class="stat-tile__icon"><i class="fas fa-receipt"></i></span>
                        <span class="stat-tile__body">
                            <span class="stat-tile__label">Expenses today</span>
                            <span class="stat-tile__value">{{ money($today['expenses_total']) }}</span>
                        </span>
                    </a>
                </div>
            @endif

            @if ($can['order'])
                <div class="col-6 col-md-4 col-xl-3">
                    <a class="stat-tile {{ $today['pending_orders'] > 0 ? 'stat-tile--info' : 'stat-tile--muted' }}"
                       href="{{ route('admin.orders.pending') }}">
                        <span class="stat-tile__icon"><i class="fas fa-hourglass-half"></i></span>
                        <span class="stat-tile__body">
                            <span class="stat-tile__label">Orders waiting</span>
                            <span class="stat-tile__value">{{ number_format($today['pending_orders']) }}</span>
                        </span>
                    </a>
                </div>
            @endif
        </div>
    @endif

    @if ($can['cash'] || $can['sale'] || $can['purchase'])
        <p class="section-label">Money position</p>

        <div class="row">
            @if ($can['cash'])
                <div class="col-6 col-md-4 col-xl-3">
                    <a class="stat-tile stat-tile--success" href="{{ route('admin.cash-bank.index') }}">
                        <span class="stat-tile__icon"><i class="fas fa-wallet"></i></span>
                        <span class="stat-tile__body">
                            <span class="stat-tile__label">Cash in hand</span>
                            <span class="stat-tile__value">{{ money($position['cash_balance']) }}</span>
                        </span>
                    </a>
                </div>

                <div class="col-6 col-md-4 col-xl-3">
                    <a class="stat-tile stat-tile--info" href="{{ route('admin.cash-bank.index') }}">
                        <span class="stat-tile__icon"><i class="fas fa-university"></i></span>
                        <span class="stat-tile__body">
                            <span class="stat-tile__label">Bank balance</span>
                            <span class="stat-tile__value">{{ money($position['bank_balance']) }}</span>
                        </span>
                    </a>
                </div>
            @endif

            @if ($can['sale'])
                <div class="col-6 col-md-4 col-xl-3">
                    <a class="stat-tile {{ $position['receivable'] > 0 ? 'stat-tile--primary' : 'stat-tile--muted' }}"
                       href="{{ route('admin.customer-payments.index') }}">
                        <span class="stat-tile__icon"><i class="fas fa-hand-holding-usd"></i></span>
                        <span class="stat-tile__body">
                            <span class="stat-tile__label">Customers owe us</span>
                            <span class="stat-tile__value">{{ money($position['receivable']) }}</span>
                        </span>
                    </a>
                </div>
            @endif

            @if ($can['purchase'])
                <div class="col-6 col-md-4 col-xl-3">
                    <a class="stat-tile {{ $position['payable'] > 0 ? 'stat-tile--danger' : 'stat-tile--muted' }}"
                       href="{{ route('admin.supplier-payments.index') }}">
                        <span class="stat-tile__icon"><i class="fas fa-file-invoice-dollar"></i></span>
                        <span class="stat-tile__body">
                            <span class="stat-tile__label">We owe suppliers</span>
                            <span class="stat-tile__value">{{ money($position['payable']) }}</span>
                        </span>
                    </a>
                </div>
            @endif
        </div>
    @endif

    @if ($can['product'] || $can['stock'] || $can['client'] || $can['user'] || $can['department'])
        <p class="section-label">Catalogue &amp; people</p>

        <div class="row">
            @if ($can['product'])
                <div class="col-6 col-md-4 col-xl-3">
                    <a class="stat-tile stat-tile--primary" href="{{ route('admin.products.index') }}">
                        <span class="stat-tile__icon"><i class="fas fa-box-open"></i></span>
                        <span class="stat-tile__body">
                            <span class="stat-tile__label">Products</span>
                            <span class="stat-tile__value">{{ number_format($catalogue['products']) }}</span>
                        </span>
                    </a>
                </div>
            @endif

            @if ($can['stock'])
                <div class="col-6 col-md-4 col-xl-3">
                    <a class="stat-tile stat-tile--muted" href="{{ route('admin.stocks.index') }}">
                        <span class="stat-tile__icon"><i class="fas fa-warehouse"></i></span>
                        <span class="stat-tile__body">
                            <span class="stat-tile__label">Stock value</span>
                            <span class="stat-tile__value">{{ money($catalogue['stock_value']) }}</span>
                            <span class="stat-tile__meta">{{ qty($catalogue['stock_units']) }} units at cost</span>
                        </span>
                    </a>
                </div>

                <div class="col-6 col-md-4 col-xl-3">
                    <a class="stat-tile {{ $catalogue['low_stock'] > 0 ? 'stat-tile--danger' : 'stat-tile--muted' }}"
                       href="{{ route('admin.stocks.low-quantity') }}">
                        <span class="stat-tile__icon"><i class="fas fa-exclamation-triangle"></i></span>
                        <span class="stat-tile__body">
                            <span class="stat-tile__label">Low stock</span>
                            <span class="stat-tile__value">{{ number_format($catalogue['low_stock']) }}</span>
                            <span class="stat-tile__meta">{{ Str::plural('product', $catalogue['low_stock']) }} to reorder</span>
                        </span>
                    </a>
                </div>
            @endif

            @if ($can['client'])
                <div class="col-6 col-md-4 col-xl-3">
                    <a class="stat-tile stat-tile--info" href="{{ route('admin.clients.index') }}">
                        <span class="stat-tile__icon"><i class="fas fa-handshake"></i></span>
                        <span class="stat-tile__body">
                            <span class="stat-tile__label">Clients &amp; agents</span>
                            <span class="stat-tile__value">{{ number_format($catalogue['clients']) }}</span>
                        </span>
                    </a>
                </div>
            @endif

            @if ($can['user'])
                <div class="col-6 col-md-4 col-xl-3">
                    <a class="stat-tile stat-tile--muted" href="{{ route('admin.employees.index') }}">
                        <span class="stat-tile__icon"><i class="fas fa-user-tie"></i></span>
                        <span class="stat-tile__body">
                            <span class="stat-tile__label">Employees</span>
                            <span class="stat-tile__value">{{ number_format($team['employees']) }}</span>
                        </span>
                    </a>
                </div>
            @endif

            @if ($can['department'])
                <div class="col-6 col-md-4 col-xl-3">
                    <a class="stat-tile stat-tile--muted" href="{{ route('admin.departments.index') }}">
                        <span class="stat-tile__icon"><i class="fas fa-building"></i></span>
                        <span class="stat-tile__body">
                            <span class="stat-tile__label">Departments</span>
                            <span class="stat-tile__value">{{ number_format($team['departments']) }}</span>
                        </span>
                    </a>
                </div>
            @endif
        </div>
    @endif

    @if ($can['sale'] || $can['purchase'] || $can['order'] || $can['report'])
        <div class="card">
            <div class="card-body page-actions">
                @if ($can['sale'])
                    <a href="{{ route('admin.sales.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus mr-1"></i> New sale
                    </a>
                @endif
                @if ($can['purchase'])
                    <a href="{{ route('admin.purchases.create') }}" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-plus mr-1"></i> New purchase
                    </a>
                @endif
                @if ($can['order'])
                    <a href="{{ route('admin.orders.pending') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-shopping-cart mr-1"></i> Pending orders
                    </a>
                @endif
                @if ($can['report'])
                    <a href="{{ route('admin.reports.index') }}" class="btn btn-secondary btn-sm ml-auto">
                        <i class="fas fa-chart-bar mr-1"></i> Reports
                    </a>
                @endif
            </div>
        </div>
    @endif

    <div class="row">
        @if ($can['sale'])
            <div class="col-xl-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Sales, last 30 days</h3>
                    </div>
                    <div class="card-body">
                        <canvas id="salesChart" height="90"></canvas>
                    </div>
                </div>
            </div>
        @endif

        @if ($can['order'])
            <div class="col-xl-4">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Orders by status</h3>
                    </div>
                    <div class="card-body">
                        @if (empty($orderStatusDistribution['labels']))
                            <div class="empty-state">
                                <i class="fas fa-shopping-cart"></i>
                                <p>No orders yet.</p>
                            </div>
                        @else
                            <canvas id="orderStatusChart" height="180"></canvas>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    </div>

    <div class="row">
        @if ($can['sale'])
            <div class="col-xl-5">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Best sellers</h3>
                    </div>
                    <div class="card-body">
                        @if (empty($topProducts))
                            <div class="empty-state">
                                <i class="fas fa-trophy"></i>
                                <p>Nothing sold yet.</p>
                            </div>
                        @else
                            <canvas id="topProductsChart" height="190"></canvas>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        <div class="{{ $can['sale'] ? 'col-xl-7' : 'col-12' }}">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Latest activity</h3>
                </div>
                <div class="card-body p-0" style="max-height: 340px; overflow-y: auto;">
                    @if (empty($recentActivities))
                        <div class="empty-state">
                            <i class="fas fa-stream"></i>
                            <p>Nothing has happened yet.</p>
                        </div>
                    @else
                        <table class="table table-hover table-sm mb-0">
                            <tbody>
                                @foreach ($recentActivities as $activity)
                                    <tr>
                                        <td style="width: 84px">
                                            <span class="badge badge-soft-{{ $activity['tone'] }}">{{ $activity['type'] }}</span>
                                        </td>
                                        <td><a href="{{ $activity['url'] }}">{{ $activity['description'] }}</a></td>
                                        <td class="text-right text-nowrap">{{ money($activity['amount']) }}</td>
                                        <td class="text-muted text-right text-nowrap" style="width: 110px">
                                            {{ \Illuminate\Support\Carbon::parse($activity['date'])->diffForHumans(short: true) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script>
        $(function () {
            if (typeof Chart === 'undefined') {
                return;
            }

            Chart.defaults.font.family = 'Inter, "Segoe UI", system-ui, sans-serif';
            Chart.defaults.font.size = 11;
            Chart.defaults.color = '#94a3b8';

            const gridline = { color: 'rgba(148, 163, 184, .18)', drawBorder: false };

            // Each canvas is only rendered when the viewer holds the matching
            // permission and there is data to show, so guard before drawing.
            const salesCanvas = document.getElementById('salesChart');
            if (salesCanvas) {
                new Chart(salesCanvas, {
                    type: 'line',
                    data: {
                        labels: @json($salesChart['labels']),
                        datasets: [{
                            label: 'Sales',
                            data: @json($salesChart['totals']),
                            borderColor: '#4f46e5',
                            backgroundColor: 'rgba(79, 70, 229, .10)',
                            borderWidth: 2,
                            pointRadius: 0,
                            pointHoverRadius: 4,
                            fill: true,
                            tension: 0.35,
                        }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        interaction: { mode: 'index', intersect: false },
                        plugins: { legend: { display: false } },
                        scales: {
                            x: { grid: { display: false } },
                            y: { beginAtZero: true, grid: gridline },
                        },
                    },
                });
            }

            const orderStatusCanvas = document.getElementById('orderStatusChart');
            if (orderStatusCanvas) {
                new Chart(orderStatusCanvas, {
                    type: 'doughnut',
                    data: {
                        labels: @json($orderStatusDistribution['labels']),
                        datasets: [{
                            data: @json($orderStatusDistribution['counts']),
                            backgroundColor: ['#4f46e5', '#0284c7', '#0f9d58', '#d97706', '#dc2626', '#7c3aed', '#64748b'],
                            borderWidth: 0,
                        }],
                    },
                    options: {
                        responsive: true,
                        cutout: '62%',
                        plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, padding: 12 } } },
                    },
                });
            }

            const topProductsCanvas = document.getElementById('topProductsChart');
            if (topProductsCanvas) {
                new Chart(topProductsCanvas, {
                    type: 'bar',
                    data: {
                        labels: @json(array_column($topProducts, 'name')),
                        datasets: [{
                            label: 'Units sold',
                            data: @json(array_column($topProducts, 'qty')),
                            backgroundColor: '#4f46e5',
                            borderRadius: 4,
                            barThickness: 16,
                        }],
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        plugins: { legend: { display: false } },
                        scales: {
                            x: { beginAtZero: true, grid: gridline },
                            y: { grid: { display: false } },
                        },
                    },
                });
            }
        });
    </script>
@endpush
