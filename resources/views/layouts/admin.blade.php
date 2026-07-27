@extends('adminlte::page')

{{--
    adminlte::page already renders @stack('css') / @stack('js') alongside
    @yield('css') / @yield('js'), so these sections must NOT re-emit the stacks
    or every @push block on a page would be output twice (duplicate event
    handlers, duplicated rows, double AJAX calls).
--}}
@section('css')
    <link rel="stylesheet" href="{{ asset('assets/css/datatables.bootstrap4.min.css') }}">
@endsection

@php
    $pageTitle = trim((string) ($title ?? View::getSection('content_title') ?? ''));
@endphp

@section('title', ($pageTitle !== '' ? $pageTitle.' — ' : '').\App\Support\Branding::name())

@section('content_header')
    <h1>@yield('content_title', $title ?? 'Dashboard')</h1>
@endsection

@section('content_top_nav_left')
    {{--
        Global lookup. The packaged navbar-search item posts to a page; this one
        queries admin.search.global and shows the hits inline, which is what the
        endpoint was built to return.
    --}}
    <li class="nav-item d-none d-md-block">
        <div class="global-search">
            <i class="fas fa-search global-search__icon"></i>
            <input type="search" id="global-search" class="form-control" autocomplete="off"
                placeholder="Search products, clients, invoices…"
                data-url="{{ route('admin.search.global') }}">
            <div class="global-search__results" id="global-search-results" hidden></div>
        </div>
    </li>
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
    @yield('content_body')
@endsection

@section('footer')
    <span>&copy; {{ now()->year }} {{ \App\Support\Branding::name() }}</span>
@endsection

@section('js')
    <script src="{{ asset('assets/js/datatables.min.js') }}"></script>
    <script src="{{ asset('assets/js/datatables.bootstrap4.min.js') }}"></script>

    {{--
        Flash messages are handed to the toast layer instead of rendering an
        inline alert block, so a redirect result never pushes the page content
        down. Validation errors stay inline next to their field.
    --}}
    @php
        $flashes = [];

        foreach (['success', 'error', 'warning', 'info'] as $level) {
            if (filled(session($level))) {
                $flashes[] = ['type' => $level, 'message' => session($level)];
            }
        }

        if ($errors->any()) {
            $flashes[] = [
                'type' => 'error',
                'message' => $errors->count() === 1
                    ? $errors->first()
                    : $errors->count().' fields need your attention.',
            ];
        }
    @endphp

    <script>
        window.ERP_FLASH = @json($flashes);
    </script>

    <script>
        $(function () {
            const $input = $('#global-search');
            const $results = $('#global-search-results');

            if (! $input.length) {
                return;
            }

            let timer = null;
            let requestId = 0;

            function hide() {
                $results.attr('hidden', true).empty();
            }

            function render(items) {
                if (! items.length) {
                    $results.html('<p class="global-search__empty">Nothing found.</p>').removeAttr('hidden');

                    return;
                }

                $results.empty();

                items.forEach(function (item) {
                    $('<a>')
                        .attr('href', item.url)
                        .addClass('global-search__hit')
                        .append($('<span class="global-search__type">').text(item.type))
                        .append($('<span>').text(item.label))
                        .appendTo($results);
                });

                $results.removeAttr('hidden');
            }

            $input.on('input', function () {
                const term = $input.val().trim();

                window.clearTimeout(timer);

                if (term.length < 2) {
                    hide();

                    return;
                }

                // Debounced, and stale responses are discarded so a slow reply
                // for an earlier term cannot overwrite a newer result set.
                timer = window.setTimeout(function () {
                    const current = ++requestId;

                    $.getJSON($input.data('url'), { q: term })
                        .done(function (items) {
                            if (current === requestId) {
                                render(items);
                            }
                        })
                        .fail(function () {
                            if (current === requestId) {
                                hide();
                            }
                        });
                }, 250);
            });

            $input.on('keydown', function (event) {
                if (event.key === 'Escape') {
                    hide();
                    $input.trigger('blur');
                }
            });

            $(document).on('click', function (event) {
                if (! $(event.target).closest('.global-search').length) {
                    hide();
                }
            });
        });
    </script>
@endsection
