@extends('errors.layout', [
    'code' => 429,
    'title' => 'Too many requests',
    'message' => 'You have made a lot of requests in a short time. Wait a moment before trying again.',
    'tone' => '#d97706',
    'toneSoft' => '#fdf3e3',
])

@section('glyph')
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
        <path d="M13 2 3 14h9l-1 8 10-12h-9l1-8z"/>
    </svg>
@endsection

@section('actions')
    <button class="btn btn--primary" type="button" onclick="location.reload()">Try again</button>
    <a class="btn btn--ghost" href="{{ url('/') }}">Go to dashboard</a>
@endsection
