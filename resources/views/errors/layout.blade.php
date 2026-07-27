{{--
    Shared shell for every HTTP error page.

    Deliberately self-contained: styles are inline and nothing is read from the
    database beyond a guarded branding lookup, so a 500 caused by a broken
    database or a missing asset build still renders a usable page.

    Child pages provide: $code, $title, $message, $icon, $tone
    and may override the `actions` section.
--}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $code }} — {{ $title }}</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <style>
        :root {
            --ink: #0f172a;
            --ink-soft: #475569;
            --muted: #94a3b8;
            --line: #e6e9f0;
            --canvas: #f4f6fb;
            --primary: #4f46e5;
            --primary-600: #4338ca;
            --tone: {{ $tone ?? '#4f46e5' }};
            --tone-soft: {{ $toneSoft ?? '#eef2ff' }};
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            background:
                radial-gradient(900px 420px at 50% -10%, #ffffff 0%, rgba(255, 255, 255, 0) 70%),
                var(--canvas);
            font-family: "Inter", "Segoe UI", system-ui, -apple-system, "Helvetica Neue", Arial, sans-serif;
            color: var(--ink);
            -webkit-font-smoothing: antialiased;
        }

        .panel {
            width: 100%;
            max-width: 520px;
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 18px;
            box-shadow: 0 24px 60px -24px rgba(15, 23, 42, .22);
            padding: 40px 36px 32px;
            text-align: center;
        }

        .brand {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            margin-bottom: 26px;
            text-decoration: none;
            color: var(--ink);
        }

        .brand__mark {
            width: 30px;
            height: 30px;
            border-radius: 8px;
            background: linear-gradient(135deg, var(--primary) 0%, #7c3aed 100%);
            color: #fff;
            font-weight: 700;
            font-size: .8rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            letter-spacing: -.02em;
        }

        .brand__name {
            font-weight: 600;
            font-size: .95rem;
            letter-spacing: -.01em;
        }

        .glyph {
            width: 62px;
            height: 62px;
            margin: 0 auto 18px;
            border-radius: 18px;
            background: var(--tone-soft);
            color: var(--tone);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .glyph svg { width: 30px; height: 30px; }

        .code {
            display: inline-block;
            font-size: .72rem;
            font-weight: 700;
            letter-spacing: .12em;
            text-transform: uppercase;
            color: var(--tone);
            background: var(--tone-soft);
            border-radius: 999px;
            padding: 4px 12px;
            margin-bottom: 14px;
        }

        h1 {
            font-size: 1.4rem;
            font-weight: 650;
            letter-spacing: -.015em;
            margin: 0 0 10px;
        }

        p.lead {
            margin: 0 auto;
            max-width: 40ch;
            font-size: .92rem;
            line-height: 1.6;
            color: var(--ink-soft);
        }

        .actions {
            margin-top: 26px;
            display: flex;
            gap: 10px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn {
            border: 1px solid transparent;
            border-radius: 8px;
            padding: 9px 18px;
            font-size: .875rem;
            font-weight: 500;
            font-family: inherit;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 7px;
            transition: background-color .12s ease, border-color .12s ease;
        }

        .btn--primary {
            background: var(--primary);
            color: #fff;
        }

        .btn--primary:hover { background: var(--primary-600); }

        .btn--ghost {
            background: #fff;
            border-color: var(--line);
            color: var(--ink-soft);
        }

        .btn--ghost:hover { background: #f8fafc; color: var(--ink); }

        .ref {
            margin-top: 24px;
            padding-top: 18px;
            border-top: 1px solid var(--line);
            font-size: .76rem;
            color: var(--muted);
        }

        .ref code {
            font-family: ui-monospace, SFMono-Regular, "SF Mono", Menlo, Consolas, monospace;
            background: #f4f6fb;
            border-radius: 5px;
            padding: 2px 6px;
            color: var(--ink-soft);
        }

        @media (max-width: 480px) {
            .panel { padding: 30px 22px 26px; }
            h1 { font-size: 1.2rem; }
        }
    </style>
</head>

<body>
    <main class="panel">
        <a class="brand" href="{{ url('/') }}">
            <span class="brand__mark">{{ \App\Support\Branding::monogram() }}</span>
            <span class="brand__name">{{ \App\Support\Branding::name() }}</span>
        </a>

        <div class="glyph">@yield('glyph')</div>

        <span class="code">Error {{ $code }}</span>

        <h1>{{ $title }}</h1>
        <p class="lead">{{ $message }}</p>

        <div class="actions">
            @section('actions')
                <a class="btn btn--primary" href="{{ url('/') }}">Go to dashboard</a>
                <button class="btn btn--ghost" type="button" onclick="history.back()">Go back</button>
            @show
        </div>

        <p class="ref">
            {{ now()->format('d M Y, H:i') }}
            @isset($reference)
                &middot; reference <code>{{ $reference }}</code>
            @endisset
        </p>
    </main>
</body>

</html>
