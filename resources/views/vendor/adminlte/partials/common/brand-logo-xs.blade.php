{{--
    Sidebar brand. Overrides the packaged partial so the mark and wordmark come
    from the Settings module instead of static config values.
--}}
@inject('layoutHelper', 'JeroenNoten\LaravelAdminLte\Helpers\LayoutHelper')

@php
    $brandUrl = route(config('adminlte.dashboard_url', 'admin.home'));
    $brandLogo = \App\Support\Branding::logoUrl();
    $brandName = \App\Support\Branding::shortName();
@endphp

<a href="{{ $brandUrl }}"
    class="{{ $layoutHelper->isLayoutTopnavEnabled() ? 'navbar-brand' : 'brand-link' }} {{ config('adminlte.classes_brand') }}">

    @if ($brandLogo)
        <img src="{{ $brandLogo }}" alt="{{ $brandName }}" class="brand-image">
    @else
        <span class="brand-mark" aria-hidden="true">{{ \App\Support\Branding::monogram() }}</span>
    @endif

    <span class="brand-text {{ config('adminlte.classes_brand_text') }}">{{ $brandName }}</span>
</a>
