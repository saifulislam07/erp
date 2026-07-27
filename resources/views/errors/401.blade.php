@extends('errors.layout', [
    'code' => 401,
    'title' => 'Sign in to continue',
    'message' => 'This page needs a signed-in account. Your session may have ended after a period of inactivity.',
    'tone' => '#0284c7',
    'toneSoft' => '#e6f4fb',
])

@section('glyph')
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
        <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/>
        <polyline points="10 17 15 12 10 7"/>
        <line x1="15" y1="12" x2="3" y2="12"/>
    </svg>
@endsection

@section('actions')
    <a class="btn btn--primary" href="{{ route('login') }}">Sign in</a>
    <a class="btn btn--ghost" href="{{ url('/') }}">Home</a>
@endsection
