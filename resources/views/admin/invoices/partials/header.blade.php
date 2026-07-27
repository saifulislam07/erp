@php
    $settings = \App\Models\Setting::allCached();
@endphp
<div class="header">
    <h1>{{ $settings['company_name'] ?? config('app.name') }}</h1>
    @if (! empty($settings['company_address']))
        <p>{{ $settings['company_address'] }}</p>
    @endif
    @if (! empty($settings['company_phone']) || ! empty($settings['company_email']))
        <p>
            @if (! empty($settings['company_phone'])) {{ $settings['company_phone'] }} @endif
            @if (! empty($settings['company_phone']) && ! empty($settings['company_email'])) | @endif
            @if (! empty($settings['company_email'])) {{ $settings['company_email'] }} @endif
        </p>
    @endif
    @if (! empty($settings['vat_registration_number']))
        <p>VAT Reg: {{ $settings['vat_registration_number'] }}</p>
    @endif
    <p>{{ $invoiceTitle }}</p>
</div>
