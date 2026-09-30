@extends('errors.layout')

@section('title', 'Session Expired')
@section('code', '419')
@section('message', 'Session Expired')

@section('illustration')
<div class="md:w-1/4 mx-auto">
    <h2 class="text-7xl p-0">😴</h2>
</div>
@endsection

@section('content')
<p>Your session has expired for security reasons. This usually happens when:</p>
<ul style="text-align: left; display: inline-block; margin: 1rem 0;">
    <li>• You've been inactive for too long</li>
    <li>• Your browser has been open for an extended period</li>
    <li>• There was a security token mismatch</li>
    <li>• You tried to submit a form after your session expired</li>
</ul>
<p>Please refresh the page and try again. If you were in the middle of something important, you may need to start over.</p>
@endsection

@section('actions')
<button onclick="reloadPage()" class="btn btn-bg
    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
    </svg>
    Refresh Page
</button>

@guest
<a href="{{ route('login') }}" class="btn btn-secondary">
    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
    </svg>
    Sign In Again
</a>

@endguest

<a href="{{ url('/') }}" class="btn btn-secondary">
    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
    </svg>
    Go Home
</a>
@endsection
