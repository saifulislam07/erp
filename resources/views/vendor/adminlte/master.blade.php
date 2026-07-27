{{--
    Application shell.

    Overrides the packaged master view so the panel serves its own asset bundle
    from /assets, carries the company identity in the title and favicon, and
    loads the ERP toast/dialog layer on every page.
--}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">

    @yield('meta_tags')

    <title>@yield('title', \App\Support\Branding::name())</title>

    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">

    @yield('adminlte_css_pre')

    <link rel="stylesheet" href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/overlayScrollbars/css/OverlayScrollbars.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/core/css/base.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/theme.css') }}?v={{ config('erp.asset_version') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/notify.css') }}?v={{ config('erp.asset_version') }}">

    @if (config('adminlte.google_fonts.allowed', true))
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link rel="stylesheet"
              href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
    @endif

    @include('adminlte::plugins', ['type' => 'css'])

    @yield('adminlte_css')
</head>

<body class="@yield('classes_body')" @yield('body_data')>

    @yield('body')

    <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('vendor/overlayScrollbars/js/jquery.overlayScrollbars.min.js') }}"></script>
    <script src="{{ asset('assets/core/js/base.min.js') }}"></script>
    <script src="{{ asset('assets/js/sweetalert2.all.min.js') }}"></script>
    <script src="{{ asset('assets/js/app.js') }}?v={{ config('erp.asset_version') }}"></script>

    @include('adminlte::plugins', ['type' => 'js'])

    @yield('adminlte_js')
</body>

</html>
