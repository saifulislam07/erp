@extends('layouts.admin')

@section('content_title', 'Dashboard')

@section('content_body')
    <div class="row">
        <div class="col-lg-4 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $cards['total_clients'] }}</h3>
                    <p>Total Clients</p>
                </div>
                <div class="icon"><i class="fas fa-users"></i></div>
            </div>
        </div>

        <div class="col-lg-4 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $cards['total_products'] }}</h3>
                    <p>Total Products</p>
                </div>
                <div class="icon"><i class="fas fa-box"></i></div>
            </div>
        </div>

        <div class="col-lg-4 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $cards['total_stock_value'] }}</h3>
                    <p>Total Stock Value</p>
                </div>
                <div class="icon"><i class="fas fa-warehouse"></i></div>
            </div>
        </div>

        <div class="col-lg-4 col-6">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3>{{ $cards['todays_sales'] }}</h3>
                    <p>Today's Sales</p>
                </div>
                <div class="icon"><i class="fas fa-cash-register"></i></div>
            </div>
        </div>

        <div class="col-lg-4 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $cards['pending_orders'] }}</h3>
                    <p>Pending Orders</p>
                </div>
                <div class="icon"><i class="fas fa-shopping-cart"></i></div>
            </div>
        </div>

        <div class="col-lg-4 col-6">
            <div class="small-box bg-secondary">
                <div class="inner">
                    <h3>{{ $cards['low_stock_alerts'] }}</h3>
                    <p>Low Stock Alerts</p>
                </div>
                <div class="icon"><i class="fas fa-exclamation-triangle"></i></div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ number_format($cashWidgets['cash_balance'], 2) }}</h3>
                    <p>Cash Balance</p>
                </div>
                <div class="icon"><i class="fas fa-money-bill-wave"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ number_format($cashWidgets['bank_balance'], 2) }}</h3>
                    <p>Bank Balance</p>
                </div>
                <div class="icon"><i class="fas fa-university"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3>{{ number_format($cards['todays_sales'], 2) }}</h3>
                    <p>Today's Sales Revenue</p>
                </div>
                <div class="icon"><i class="fas fa-chart-line"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ number_format($cashWidgets['todays_expenses'], 2) }}</h3>
                    <p>Today's Expenses</p>
                </div>
                <div class="icon"><i class="fas fa-receipt"></i></div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <a href="{{ route('admin.sales.create') }}" class="btn btn-primary mr-2"><i class="fas fa-cash-register mr-1"></i> New Sale</a>
                    <a href="{{ route('admin.purchases.create') }}" class="btn btn-success mr-2"><i class="fas fa-truck-loading mr-1"></i> New Purchase</a>
                    <a href="{{ route('admin.orders.index') }}" class="btn btn-warning mr-2"><i class="fas fa-shopping-cart mr-1"></i> View Orders</a>
                    <a href="{{ route('admin.reports.index') }}" class="btn btn-info"><i class="fas fa-chart-bar mr-1"></i> View Reports</a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Sales - Last 30 Days</h3>
                </div>
                <div class="card-body">
                    <canvas id="salesChart" height="100"></canvas>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Order Status Distribution</h3>
                </div>
                <div class="card-body">
                    <canvas id="orderStatusChart" height="150"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Top 5 Selling Products</h3>
                </div>
                <div class="card-body">
                    <canvas id="topProductsChart" height="150"></canvas>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Recent Activities</h3>
                </div>
                <div class="card-body p-0" style="max-height: 320px; overflow-y: auto;">
                    @if (empty($recentActivities))
                        <p class="p-3 text-muted mb-0">No recent activity.</p>
                    @else
                        <table class="table table-sm mb-0">
                            <tbody>
                                @foreach ($recentActivities as $activity)
                                    <tr>
                                        <td><span class="badge badge-secondary">{{ $activity['type'] }}</span></td>
                                        <td>{{ $activity['description'] }}</td>
                                        <td class="text-right">{{ number_format($activity['amount'], 2) }}</td>
                                        <td class="text-muted text-sm">{{ \Illuminate\Support\Carbon::parse($activity['date'])->diffForHumans() }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Departments</h3>
                </div>
                <div class="card-body">
                    <h2>{{ $totalDepartments }}</h2>
                    <p class="text-muted">Total departments</p>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Employees</h3>
                </div>
                <div class="card-body">
                    <h2>{{ $totalEmployees }}</h2>
                    <p class="text-muted">Total employees</p>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script>
        $(function () {
            new Chart(document.getElementById('salesChart'), {
                type: 'line',
                data: {
                    labels: @json($salesChart['labels']),
                    datasets: [{
                        label: 'Sales',
                        data: @json($salesChart['totals']),
                        borderColor: '#007bff',
                        backgroundColor: 'rgba(0,123,255,0.1)',
                        fill: true,
                        tension: 0.3,
                    }],
                },
                options: { responsive: true, plugins: { legend: { display: false } } },
            });

            new Chart(document.getElementById('orderStatusChart'), {
                type: 'pie',
                data: {
                    labels: @json($orderStatusDistribution['labels']),
                    datasets: [{
                        data: @json($orderStatusDistribution['counts']),
                        backgroundColor: ['#17a2b8', '#ffc107', '#28a745', '#dc3545', '#6c757d', '#007bff', '#fd7e14'],
                    }],
                },
                options: { responsive: true },
            });

            new Chart(document.getElementById('topProductsChart'), {
                type: 'bar',
                data: {
                    labels: @json(array_column($topProducts, 'name')),
                    datasets: [{
                        label: 'Quantity Sold',
                        data: @json(array_column($topProducts, 'qty')),
                        backgroundColor: '#28a745',
                    }],
                },
                options: { responsive: true, plugins: { legend: { display: false } } },
            });
        });
    </script>
@endpush
