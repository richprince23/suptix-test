@extends('errors.layout')

@section('title', 'Error')
@section('code', $exception->getStatusCode() ?? '???')
@section('message', $exception->getMessage() ?: 'Something went wrong')

@section('illustration')
<svg fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
</svg>
@endsection

@section('content')
<p>An unexpected error occurred while processing your request.</p>
<p>Our team has been notified and is working to resolve this issue. Please try again later.</p>
{{--
@if (config('app.debug') && isset($exception))
<div style="margin-top: 1.5rem; padding: 1rem; background: #f3f4f6; border-radius: 8px; text-align: left;">
    <details>
        <summary style="cursor: pointer; font-weight: 600; color: #374151;">Technical Details</summary>
        <div style="margin-top: 0.5rem; font-family: monospace; font-size: 0.875rem; color: #6b7280;">
            <strong>Error:</strong> {{ $exception->getMessage() }}<br>
            <strong>File:</strong> {{ $exception->getFile() ?? 'Unknown' }}<br>
            <strong>Line:</strong> {{ $exception->getLine() ?? 'Unknown' }}
        </div>
    </details>
</div>
@endif --}}
@endsection

@section('actions')
{{-- <button onclick="reloadPage()" class="btn btn-bg">
    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
    </svg>
    Try Again
</button> --}}

<button onclick="goBack()" class="btn btn-secondary">
    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
    </svg>
    Go Back
</button>

{{-- <a href="{{ url('/') }}" class="btn btn-secondary">
    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
    </svg>
    Go Home
</a> --}}

<button onclick="reportIssue()" class="btn btn-secondary">
    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
    </svg>
    Report Issue
</button>
@endsection
