{{--
    Shared document shell for every printable invoice.

    Rendered twice from the same markup: once in the browser (where the toolbar
    and Print button show) and once through DomPDF (where they are hidden). That
    constrains the CSS to what DomPDF understands — tables for layout, no
    flexbox or grid, no CSS variables.

    Child views provide: $documentTitle, $documentNumber, $accentColor
    and the sections: meta, items, summary, footnote
--}}
@php
    $settings = \App\Models\Setting::allCached();
    $logo = \App\Support\Branding::logoUrl();
    $isPdf = ($pdf ?? false) || request()->query('format') === 'pdf';
@endphp

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $documentTitle }} {{ $documentNumber }}</title>
    <style>
        @page { margin: 18mm 14mm; }

        body {
            font-family: "DejaVu Sans", sans-serif;
            font-size: 11px;
            line-height: 1.5;
            color: #1f2937;
            margin: 0;
            background: #f4f6fb;
        }

        .sheet {
            background: #ffffff;
            max-width: 820px;
            margin: 0 auto;
            padding: 28px 32px 32px;
        }

        /* ------------------------------------------------------- toolbar */
        .toolbar {
            max-width: 820px;
            margin: 16px auto 12px;
            text-align: right;
        }

        .toolbar a, .toolbar button {
            display: inline-block;
            border: 1px solid #d8dde8;
            background: #ffffff;
            color: #475569;
            border-radius: 7px;
            padding: 7px 14px;
            font-size: 12px;
            font-family: inherit;
            text-decoration: none;
            cursor: pointer;
            margin-left: 6px;
        }

        .toolbar a.primary, .toolbar button.primary {
            background: {{ $accentColor ?? '#4f46e5' }};
            border-color: {{ $accentColor ?? '#4f46e5' }};
            color: #ffffff;
        }

        /* -------------------------------------------------------- header */
        table { border-collapse: collapse; width: 100%; }

        .masthead td { vertical-align: top; padding: 0; }

        .company-name {
            font-size: 17px;
            font-weight: bold;
            color: #0f172a;
            margin: 0 0 3px;
        }

        .company-detail {
            color: #64748b;
            font-size: 10px;
            margin: 0;
        }

        .logo { max-height: 54px; max-width: 190px; }

        .doc-type {
            font-size: 19px;
            font-weight: bold;
            letter-spacing: .06em;
            text-transform: uppercase;
            color: {{ $accentColor ?? '#4f46e5' }};
            margin: 0 0 2px;
        }

        .doc-number { font-size: 12px; color: #475569; margin: 0; }

        .rule {
            border: 0;
            border-top: 2px solid {{ $accentColor ?? '#4f46e5' }};
            margin: 14px 0 16px;
        }

        /* ---------------------------------------------------------- meta */
        .meta { margin-bottom: 18px; }
        .meta td { vertical-align: top; padding: 0 14px 0 0; width: 33.33%; }

        .meta-label {
            font-size: 9px;
            font-weight: bold;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: #94a3b8;
            display: block;
            margin-bottom: 3px;
        }

        .meta-value { font-size: 11px; color: #1f2937; }
        .meta-value strong { color: #0f172a; }

        /* --------------------------------------------------------- items */
        table.items { margin-bottom: 16px; }

        table.items th {
            background: #f1f4f9;
            border-bottom: 1px solid #d8dde8;
            color: #475569;
            font-size: 9px;
            font-weight: bold;
            letter-spacing: .06em;
            text-transform: uppercase;
            padding: 7px 8px;
            text-align: left;
        }

        table.items td {
            border-bottom: 1px solid #eef1f6;
            padding: 7px 8px;
            vertical-align: top;
        }

        table.items .num { text-align: right; white-space: nowrap; }
        table.items tbody tr:last-child td { border-bottom: 1px solid #d8dde8; }

        .line-note { color: #94a3b8; font-size: 9px; display: block; }

        /* ------------------------------------------------------- summary */
        .totals td { vertical-align: top; padding: 0; }

        table.summary { width: 100%; }
        table.summary td { padding: 4px 8px; font-size: 11px; }
        table.summary td.label { color: #64748b; }
        table.summary td.value { text-align: right; white-space: nowrap; }

        table.summary tr.grand td {
            border-top: 1px solid #d8dde8;
            border-bottom: 2px solid {{ $accentColor ?? '#4f46e5' }};
            font-size: 13px;
            font-weight: bold;
            color: #0f172a;
            padding-top: 7px;
            padding-bottom: 7px;
        }

        table.summary tr.due td { color: #b91c1c; font-weight: bold; }

        .in-words {
            background: #f8fafc;
            border-left: 3px solid {{ $accentColor ?? '#4f46e5' }};
            padding: 8px 10px;
            font-size: 10px;
            color: #475569;
            margin-bottom: 16px;
        }

        .in-words strong { color: #0f172a; }

        /* --------------------------------------------------------- stamp */
        .stamp {
            display: inline-block;
            border: 2px solid;
            border-radius: 5px;
            padding: 4px 12px;
            font-size: 12px;
            font-weight: bold;
            letter-spacing: .1em;
            text-transform: uppercase;
        }

        .stamp-paid { color: #0f9d58; border-color: #0f9d58; }
        .stamp-partial { color: #d97706; border-color: #d97706; }
        .stamp-unpaid { color: #dc2626; border-color: #dc2626; }

        /* -------------------------------------------------------- footer */
        .signatures { margin-top: 42px; }

        .signatures td {
            width: 33%;
            padding-top: 26px;
            font-size: 10px;
            color: #64748b;
            text-align: center;
        }

        .signatures td span {
            display: block;
            border-top: 1px solid #94a3b8;
            padding-top: 5px;
            margin: 0 12px;
        }

        .footnote {
            margin-top: 22px;
            padding-top: 10px;
            border-top: 1px solid #eef1f6;
            font-size: 9px;
            color: #94a3b8;
            text-align: center;
        }

        @media print {
            body { background: #ffffff; }
            .toolbar { display: none; }
            .sheet { max-width: none; padding: 0; }
        }
    </style>
</head>

<body>
    @unless ($isPdf)
        <div class="toolbar">
            <button type="button" class="primary" onclick="window.print()">Print</button>
            <a href="{{ request()->fullUrlWithQuery(['format' => 'pdf']) }}">Download PDF</a>
            <a href="#" onclick="history.back(); return false;">Back</a>
        </div>
    @endunless

    <div class="sheet">
        <table class="masthead">
            <tr>
                <td style="width: 60%">
                    @if ($logo && $isPdf === false)
                        <img src="{{ $logo }}" alt="" class="logo">
                    @endif
                    <p class="company-name">{{ $settings['company_name'] ?? config('app.name') }}</p>
                    @if (! empty($settings['company_address']))
                        <p class="company-detail">{{ $settings['company_address'] }}</p>
                    @endif
                    <p class="company-detail">
                        @if (! empty($settings['company_phone'])){{ $settings['company_phone'] }}@endif
                        @if (! empty($settings['company_phone']) && ! empty($settings['company_email'])) &middot; @endif
                        @if (! empty($settings['company_email'])){{ $settings['company_email'] }}@endif
                    </p>
                    @if (! empty($settings['vat_registration_number']))
                        <p class="company-detail">VAT reg. {{ $settings['vat_registration_number'] }}</p>
                    @endif
                </td>
                <td style="width: 40%; text-align: right;">
                    <p class="doc-type">{{ $documentTitle }}</p>
                    <p class="doc-number">{{ $documentNumber }}</p>
                    {{--
                        The currency is stated once here rather than on every
                        row: DomPDF's bundled font has no glyph for some
                        currency symbols, so repeating it would print a row of
                        empty boxes down the document.
                    --}}
                    <p class="doc-number">All amounts in {{ trim(\App\Support\Branding::currency()) }}</p>
                    @hasSection('stamp')
                        <div style="margin-top: 8px;">@yield('stamp')</div>
                    @endif
                </td>
            </tr>
        </table>

        <hr class="rule">

        @yield('meta')
        @yield('items')
        @yield('summary')

        <table class="signatures">
            <tr>
                <td><span>Prepared by</span></td>
                <td><span>Authorised signature</span></td>
                <td><span>Received by</span></td>
            </tr>
        </table>

        <p class="footnote">
            @hasSection('footnote')
                @yield('footnote')
            @else
                This is a computer-generated document.
            @endif
            &middot; Generated {{ now()->format('d M Y, H:i') }}
        </p>
    </div>
</body>

</html>
