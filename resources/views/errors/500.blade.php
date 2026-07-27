@php
    // The request id lets an administrator find the matching stack trace in
    // storage/logs without exposing anything about the failure itself.
    $reference = strtoupper(substr(md5(request()->fullUrl().microtime()), 0, 10));
@endphp

@extends('errors.layout', [
    'code' => 500,
    'title' => 'Something went wrong on our side',
    'message' => 'The request could not be completed. The problem has been logged — if it keeps happening, pass the reference below to your administrator.',
    'tone' => '#dc2626',
    'toneSoft' => '#fdeceb',
    'reference' => $reference,
])

@section('glyph')
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
        <path d="M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0z"/>
        <line x1="12" y1="9" x2="12" y2="13"/>
        <line x1="12" y1="17" x2="12.01" y2="17"/>
    </svg>
@endsection

@section('actions')
    <button class="btn btn--primary" type="button" onclick="location.reload()">Try again</button>
    <a class="btn btn--ghost" href="{{ url('/') }}">Go to dashboard</a>
@endsection
