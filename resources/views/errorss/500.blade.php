@extends('errors.layout')

@section('title', 'Server Error')
@section('code', '500')
@section('message', 'Internal Server Error')

@section('illustration')
<div class="w-1/4 mx-auto">
    <h2 class="text-7xl p-0">☹️</h2>
</div>
@endsection

@section('content')
<p>Whoops! Something went wrong on our end. Our servers are experiencing some technical difficulties.</p>
<p>Don't worry, our team has been automatically notified and we're working on fixing this issue. Please try again in a few moments.</p>
<p>If the problem persists, you can contact our support team for immediate assistance.</p>
@endsection

@section('actions')
<button onclick="reloadPage()" class="btn btn-bg">
    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
    </svg>
    Try Again
</button>

<a href="{{ url('/') }}" class="btn btn-secondary">
    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
    </svg>
    Go Home
</a>

<button onclick="reportIssue()" class="btn btn-secondary">
    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
    </svg>
    Report Issue
</button>

@endsection
