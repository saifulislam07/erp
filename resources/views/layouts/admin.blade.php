@extends('adminlte::page')

{{--
    adminlte::page already renders @stack('css') / @stack('js') alongside
    @yield('css') / @yield('js'), so these sections must NOT re-emit the stacks
    or every @push block on a page would be output twice (duplicate event
    handlers, duplicated rows, double AJAX calls).
--}}
@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.11/css/dataTables.bootstrap4.min.css">
@endsection

@section('content_header')
    <h1>@yield('content_title', $title ?? 'Dashboard')</h1>
@endsection

@section('content_top_nav_right')
    <x-adminlte-navbar-notification
        id="admin-notification-bell"
        icon="far fa-bell"
        badge-color="danger"
        enable-dropdown-mode="true"
        dropdown-footer-label="See All Notifications"
        href="{{ route('admin.notifications.index') }}"
        :update-cfg="['route' => 'admin.notifications.poll', 'period' => 30]"
    />
@endsection

@section('content')
    @if (session('success'))
        <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            {{ session('error') }}
        </div>
    @endif

    @yield('content_body')
@endsection

@section('js')
    <script src="https://cdn.datatables.net/1.13.11/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.11/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endsection
