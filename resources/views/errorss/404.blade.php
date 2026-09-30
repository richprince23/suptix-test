@extends('errors.layout')

@section('title', 'Page Not Found')
@section('code', '404')
@section('message', 'Page Not Found')

@section('illustration')
<div class="">
    <h2 class="text-7xl p-0">☹️</h2>
</div>
@endsection

@section('content')
<p>Oops! The page you're looking for seems to have gone missing. It might have been moved, deleted, or you entered the wrong URL.</p>
@endsection

@section('actions')
<a href="{{ url('/') }}" class="btn btn-bg">
    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
    </svg>
    Go Home
</a>

<button onclick="goBack()" class="btn btn-secondary">
    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
    </svg>
    Go Back
</button>

@endsection
