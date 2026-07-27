@extends('errors.layout', [
    'code' => 419,
    'title' => 'This form has expired',
    'message' => 'The page sat open long enough for its security token to expire. Reload and submit again — nothing was saved.',
    'tone' => '#d97706',
    'toneSoft' => '#fdf3e3',
])

@section('glyph')
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
        <circle cx="12" cy="12" r="9"/>
        <polyline points="12 7 12 12 15 14"/>
    </svg>
@endsection

@section('actions')
    <button class="btn btn--primary" type="button" onclick="location.reload()">Reload page</button>
    <a class="btn btn--ghost" href="{{ url('/') }}">Go to dashboard</a>
@endsection
