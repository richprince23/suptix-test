@extends('errors.layout')

@section('title', 'Too Many Requests')
@section('code', '429')
@section('message', 'Too Many Requests')

@section('illustration')
<svg fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
</svg>
@endsection

@section('content')
<p>Whoa there! You're making too many requests too quickly. Our system has temporarily limited your access to protect our servers.</p>
<p>This happens when:</p>
<ul style="text-align: left; display: inline-block; margin: 1rem 0;">
    <li>• You're clicking or refreshing too rapidly</li>
    <li>• Multiple requests are being sent simultaneously</li>
    <li>• You've exceeded the rate limit for your account</li>
</ul>
<p>Please wait a moment before trying again. The limit will reset shortly.</p>
@endsection

@section('actions')
<button onclick="setTimeout(() => reloadPage(), 3000); this.disabled = true; this.innerHTML = 'Waiting... <span id=countdown>3</span>s';" class="btn btn-bg">
    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
    </svg>
    Wait & Retry
</button>

<a href="{{ url('/') }}" class="btn btn-secondary">
    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
    </svg>
    Go Home
</a>


@endsection

@section('styles')
<style>
    .error-header {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    }
</style>
@endsection

@push('scripts')
<script>
    // Countdown timer for rate limit
    let countdown = 3;
    const countdownElement = document.getElementById('countdown');

    if (countdownElement) {
        const timer = setInterval(() => {
            countdown--;
            if (countdownElement) {
                countdownElement.textContent = countdown;
            }
            if (countdown <= 0) {
                clearInterval(timer);
            }
        }, 1000);
    }
</script>
@endpush
