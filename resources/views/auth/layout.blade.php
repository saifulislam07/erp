{{--
    Sign-in shell.

    Standalone rather than built on the admin layout: these pages are shown to
    people who are not signed in, so they must not load the panel's sidebar,
    menu queries or notification polling.

    Child pages provide: $heading, $subheading and the `form` section.
--}}
@php
    $company = \App\Support\Branding::name();
    $logo = \App\Support\Branding::logoUrl();
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $heading }} — {{ $company }}</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/theme.css') }}?v={{ config('erp.asset_version') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/notify.css') }}?v={{ config('erp.asset_version') }}">
    @if (config('adminlte.google_fonts.allowed', true))
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link rel="stylesheet"
              href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
    @endif
    <style>
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            font-family: var(--erp-font);
            background: var(--erp-canvas);
            color: var(--erp-ink);
            -webkit-font-smoothing: antialiased;
        }

        /* Left panel: identity and reassurance. Hidden on small screens. */
        .auth-aside {
            width: 44%;
            max-width: 560px;
            padding: 48px 52px;
            color: #fff;
            background:
                radial-gradient(700px 380px at 15% 12%, rgba(124, 58, 237, .55) 0%, rgba(124, 58, 237, 0) 60%),
                linear-gradient(160deg, #1e1b4b 0%, #312e81 45%, #4f46e5 100%);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .auth-aside__brand {
            display: flex;
            align-items: center;
            gap: 11px;
            font-size: 1.05rem;
            font-weight: 600;
        }

        .auth-aside__brand img { max-height: 38px; max-width: 170px; }

        .auth-aside__mark {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: rgba(255, 255, 255, .16);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: .9rem;
        }

        .auth-aside h2 {
            font-size: 1.85rem;
            line-height: 1.25;
            font-weight: 650;
            letter-spacing: -.02em;
            color: #fff;
            margin: 0 0 14px;
        }

        .auth-aside p {
            color: rgba(255, 255, 255, .72);
            font-size: .93rem;
            line-height: 1.65;
            max-width: 40ch;
            margin: 0;
        }

        .auth-aside ul {
            list-style: none;
            padding: 0;
            margin: 26px 0 0;
        }

        .auth-aside li {
            display: flex;
            align-items: center;
            gap: 10px;
            color: rgba(255, 255, 255, .82);
            font-size: .88rem;
            margin-bottom: 11px;
        }

        .auth-aside li i {
            width: 24px;
            height: 24px;
            border-radius: 7px;
            background: rgba(255, 255, 255, .14);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: .7rem;
            flex: 0 0 24px;
        }

        .auth-aside__foot {
            color: rgba(255, 255, 255, .5);
            font-size: .78rem;
        }

        /* Right panel: the form itself. */
        .auth-main {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 32px 24px;
        }

        .auth-card { width: 100%; max-width: 400px; }

        .auth-card__brand {
            display: none;
            align-items: center;
            gap: 10px;
            margin-bottom: 26px;
            font-weight: 600;
        }

        .auth-card h1 {
            font-size: 1.45rem;
            font-weight: 650;
            letter-spacing: -.02em;
            margin: 0 0 6px;
        }

        .auth-card__sub {
            color: var(--erp-ink-soft);
            font-size: .9rem;
            margin: 0 0 26px;
        }

        .auth-card .form-group { margin-bottom: 1.15rem; }

        .auth-card .form-control { height: calc(2.6rem + 2px); }

        .auth-card .input-group-text { background: #fff; color: var(--erp-muted); }

        .auth-card .btn-primary {
            width: 100%;
            padding: .62rem 1rem;
            font-size: .92rem;
            font-weight: 600;
        }

        .auth-links {
            margin-top: 20px;
            font-size: .855rem;
            color: var(--erp-ink-soft);
            text-align: center;
        }

        .auth-foot {
            margin-top: 30px;
            padding-top: 18px;
            border-top: 1px solid var(--erp-line);
            font-size: .76rem;
            color: var(--erp-muted);
            text-align: center;
        }

        @media (max-width: 900px) {
            .auth-aside { display: none; }
            .auth-card__brand { display: flex; }
        }
    </style>
</head>

<body>
    <aside class="auth-aside">
        <div class="auth-aside__brand">
            @if ($logo)
                <img src="{{ $logo }}" alt="{{ $company }}">
            @else
                <span class="auth-aside__mark">{{ \App\Support\Branding::monogram() }}</span>
            @endif
            <span>{{ $company }}</span>
        </div>

        <div>
            <h2>Everything your business runs on, in one place.</h2>
            <p>
                Stock, purchases, sales, returns and the money behind them — kept in
                step so the numbers you report are the numbers that happened.
            </p>

            <ul>
                <li><i class="fas fa-boxes"></i> Live stock across every store</li>
                <li><i class="fas fa-file-invoice-dollar"></i> Invoices, dues and payments in one ledger</li>
                <li><i class="fas fa-chart-line"></i> Reports built from the same records</li>
            </ul>
        </div>

        <p class="auth-aside__foot">&copy; {{ now()->year }} {{ $company }}</p>
    </aside>

    <main class="auth-main">
        <div class="auth-card">
            <div class="auth-card__brand">
                @if ($logo)
                    <img src="{{ $logo }}" alt="{{ $company }}" style="max-height: 34px;">
                @else
                    <span class="brand-mark">{{ \App\Support\Branding::monogram() }}</span>
                @endif
                <span>{{ $company }}</span>
            </div>

            <h1>{{ $heading }}</h1>
            <p class="auth-card__sub">{{ $subheading }}</p>

            @if (session('status'))
                <div class="alert alert-success">{{ session('status') }}</div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            @yield('form')

            <p class="auth-foot">
                Trouble signing in? Contact your system administrator.
            </p>
        </div>
    </main>

    <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/js/sweetalert2.all.min.js') }}"></script>
    <script src="{{ asset('assets/js/app.js') }}?v={{ config('erp.asset_version') }}"></script>
    @stack('js')
</body>

</html>
