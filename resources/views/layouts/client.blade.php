<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Client Portal') - {{ config('app.name') }}</title>

    <link rel="stylesheet" href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/adminlte.min.css') }}">
    @stack('css')
</head>
<body class="hold-transition layout-top-nav">
    <div class="wrapper">
        <nav class="main-header navbar navbar-expand navbar-white navbar-light">
            <div class="container">
                <a href="{{ route('client.dashboard') }}" class="navbar-brand">
                    <span class="brand-text font-weight-light">{{ config('app.name') }} - Client Portal</span>
                </a>

                <div class="collapse navbar-collapse">
                    <ul class="navbar-nav">
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('client.dashboard') }}">Dashboard</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('client.orders.index') }}">My Orders</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('client.orders.create') }}">New Order</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('client.returns.index') }}">My Returns</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('client.messages.index') }}">Messages</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('client.password.edit') }}">Change Password</a>
                        </li>
                    </ul>

                    <ul class="navbar-nav ml-auto">
                        <x-adminlte-navbar-notification
                            id="client-notification-bell"
                            icon="far fa-bell"
                            badge-color="danger"
                            enable-dropdown-mode="true"
                            dropdown-footer-label="See All Notifications"
                            href="{{ route('client.notifications.index') }}"
                            :update-cfg="['route' => 'client.notifications.poll', 'period' => 30]"
                        />
                        <li class="nav-item">
                            <span class="nav-link">{{ auth('client')->user()->name }}</span>
                        </li>
                        <li class="nav-item">
                            <form action="{{ route('client.logout') }}" method="post" class="form-inline">
                                @csrf
                                <button type="submit" class="btn btn-link nav-link" style="border:0;">Logout</button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        <div class="content-wrapper">
            <div class="content-header">
                <div class="container">
                    <h1>@yield('title', 'Client Portal')</h1>
                </div>
            </div>

            <section class="content">
                <div class="container">
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                            {{ session('success') }}
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                            {{ session('error') }}
                        </div>
                    @endif

                    @yield('content')
                </div>
            </section>
        </div>
    </div>

    <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('vendor/adminlte/dist/js/adminlte.min.js') }}"></script>
    @stack('js')
</body>
</html>
