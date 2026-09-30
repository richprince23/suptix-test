<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Maintenance Mode - {{ config('app.name', 'Suptix') }}</title>
    
    {{-- Favicons --}}
    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">
    
    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Instrument Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(135deg, #1e3a8a 0%, #3730a3 50%, #581c87 100%);
            color: white;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        
        .maintenance-container {
            text-align: center;
            max-width: 600px;
            padding: 2rem;
            position: relative;
            z-index: 10;
        }
        
        .logo {
            font-size: 2.5rem;
            font-weight: 600;
            margin-bottom: 2rem;
            color: #fbbf24;
        }
        
        .maintenance-icon {
            width: 120px;
            height: 120px;
            margin: 0 auto 2rem;
            animation: pulse 2s infinite;
        }
        
        .maintenance-title {
            font-size: 2.5rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }
        
        .maintenance-subtitle {
            font-size: 1.25rem;
            opacity: 0.9;
            margin-bottom: 2rem;
        }
        
        .maintenance-message {
            font-size: 1.1rem;
            line-height: 1.6;
            opacity: 0.8;
            margin-bottom: 3rem;
        }
        
        .progress-container {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50px;
            height: 8px;
            margin: 2rem 0;
            overflow: hidden;
        }
        
        .progress-bar {
            height: 100%;
            background: linear-gradient(90deg, #fbbf24, #f59e0b);
            border-radius: 50px;
            animation: progress 3s ease-in-out infinite;
        }
        
        .status-updates {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 15px;
            padding: 1.5rem;
            margin: 2rem 0;
            backdrop-filter: blur(10px);
        }
        
        .update-title {
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #fbbf24;
        }
        
        .social-links {
            margin-top: 2rem;
        }
        
        .social-links a {
            display: inline-block;
            margin: 0 1rem;
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            transition: color 0.3s;
        }
        
        .social-links a:hover {
            color: #fbbf24;
        }
        
        .background-pattern {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            opacity: 0.1;
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.1'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        
        @keyframes progress {
            0% { width: 0%; }
            50% { width: 70%; }
            100% { width: 100%; }
        }
        
        @media (max-width: 640px) {
            .maintenance-container {
                padding: 1rem;
            }
            
            .maintenance-title {
                font-size: 2rem;
            }
            
            .maintenance-subtitle {
                font-size: 1.1rem;
            }
            
            .maintenance-message {
                font-size: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="background-pattern"></div>
    
    <div class="maintenance-container">
        <div class="logo">Suptix</div>
        
        <div class="maintenance-icon">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
            </svg>
        </div>
        
        <h1 class="maintenance-title">We're Making Things Better!</h1>
        <h2 class="maintenance-subtitle">Scheduled Maintenance in Progress</h2>
        
        <div class="maintenance-message">
            <p>We're temporarily down for maintenance to improve your Suptix experience. Our team is working hard to get everything back up and running.</p>
            <p>We appreciate your patience and will be back shortly!</p>
        </div>
        
        <div class="progress-container">
            <div class="progress-bar"></div>
        </div>
        
        <div class="status-updates">
            <div class="update-title">What's happening:</div>
            <p>• Upgrading our servers for better performance<br>
               • Enhancing security features<br>
               • Optimizing the ticket booking experience</p>
        </div>
        
        <div class="social-links">
            <a href="https://twitter.com/suptix" target="_blank">Twitter</a>
            <a href="https://facebook.com/suptix" target="_blank">Facebook</a>
            <a href="mailto:support@suptix.com">Contact Support</a>
        </div>
    </div>
    
    <script>
        // Auto-refresh every 30 seconds to check if maintenance is done
        setTimeout(() => {
            window.location.reload();
        }, 30000);
        
        // Optional: Add a countdown timer if maintenance duration is known
        // You can customize this based on your maintenance schedule
    </script>
</body>
</html>