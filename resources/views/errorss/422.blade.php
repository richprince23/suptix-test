@extends('errors.layout')

@section('title', 'Unprocessable Entity')
@section('code', '422')
@section('message', 'Unprocessable Entity')

@section('illustration')
<svg fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
</svg>
@endsection

@section('content')
<p>The data you submitted couldn't be processed due to validation errors.</p>
<p>This usually happens when:</p>
<ul style="text-align: left; display: inline-block; margin: 1rem 0;">
    <li>• Required fields are missing or empty</li>
    <li>• The format of submitted data is incorrect</li>
    <li>• File uploads exceed size limits or are invalid formats</li>
    <li>• Duplicate entries are not allowed</li>
</ul>
<p>Please review your input and try again.</p>
@endsection

@section('actions')
<button onclick="goBack()" class="btn btn-bg
    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
    </svg>
    Go Back & Fix
</button>

<a href="{{ url('/') }}" class="btn btn-secondary">
    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
    </svg>
    Go Home
</a>

<button onclick="reportIssue()" class="btn btn-secondary">
    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
    </svg>
    Need Help?
</a>
@endsection
