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
