@php
    // Authorization failures often carry a helpful message ("You cannot edit a
    // settled account."); fall back to a generic line when they do not.
    $reason = trim((string) (($exception ?? null)?->getMessage() ?? ''));
@endphp

@extends('errors.layout', [
    'code' => 403,
    'title' => 'You do not have access to this',
    'message' => $reason !== '' && $reason !== 'This action is unauthorized.'
        ? $reason
        : 'Your role does not include permission for this area. Ask an administrator if you think you should have it.',
    'tone' => '#d97706',
    'toneSoft' => '#fdf3e3',
])

@section('glyph')
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
        <rect x="3" y="11" width="18" height="11" rx="2"/>
        <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
    </svg>
@endsection
