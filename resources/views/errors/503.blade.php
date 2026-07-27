@extends('errors.layout', [
    'code' => 503,
    'title' => 'Down for maintenance',
    'message' => 'The system is being updated and will be back shortly. No data is lost while this page is showing.',
    'tone' => '#0284c7',
    'toneSoft' => '#e6f4fb',
])

@section('glyph')
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
        <path d="M14.7 6.3a4 4 0 0 0 5 5l-9 9a2.8 2.8 0 0 1-4-4l9-9z"/>
        <path d="M14.7 6.3 17.5 3.5"/>
    </svg>
@endsection

@section('actions')
    <button class="btn btn--primary" type="button" onclick="location.reload()">Check again</button>
@endsection
