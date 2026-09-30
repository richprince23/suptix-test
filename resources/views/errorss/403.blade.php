@extends('errors.layout')

@section('title', 'Access Forbidden')
@section('code', '403')
@section('message', 'Access Forbidden')

@section('illustration')
<div class="md:w-1/4 mx-auto">
    <h2 class="text-7xl p-0">😵‍💫</h2>
</div>
@endsection

@section('content')
<p>Sorry, you don't have permission to access this resource. This could be because:</p>
<ul style="text-align: left; display: inline-block; margin: 1rem 0;">
    <li>• You need to be logged in with the right account</li>
    <li>• Your account doesn't have the required permissions</li>
    <li>• The resource requires special authorization</li>
</ul>
<p>If you believe this is an error, please contact our support team.</p>
@endsection

@section('actions')
@guest
<a href="{{ route('login') }}" class="btn btn-bg">
    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
    </svg>
    Sign In
</a>

@endguest

<a href="{{ url('/') }}" class="btn btn-secondary">
    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
    </svg>
    Go Home
</a>

<button onclick="reportIssue()" class="btn btn-secondary">
    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
    </svg>
    Contact Support
</button>
@endsection
