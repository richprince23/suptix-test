<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" @class(['dark' => ($appearance ?? 'system') == 'dark'])>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('title') - {{ config('app.name', 'Suptix') }}</title>

    {{-- Meta tags --}}
    <meta name="description" content="@yield('description', 'An error occurred on Suptix - Your trusted platform for event ticketing and management.')">
    <meta name="robots" content="noindex, nofollow">

    {{-- Favicons --}}
    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

    {{-- Styles --}}
    @vite(['resources/css/app.css'])

    {{-- Additional styles for error pages --}}
    <style>
        .error-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            /* background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); */
        }

        .error-card {
            max-width: 500px;
            width: 90%;
            /* background: white; */
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .error-header {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%);
            color: white;
            text-align: center;
            padding: 2rem;
        }

        .error-code {
            font-size: 4rem;
            font-weight: 600;
            margin: 0;
            line-height: 1;
        }

        .error-message {
            font-size: 1.25rem;
            margin: 0.5rem 0 0 0;
            opacity: 0.9;
        }

        .error-body {
            padding: 2rem;
            text-align: center;
        }

        .error-description {
            color: #6b7280;
            margin-bottom: 2rem;
            line-height: 1.6;
        }

        .error-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            justify-content: center;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            padding: 0.75rem 1.5rem;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.2s;
            border: none;
            cursor: pointer;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
        }

        .btn-secondary {
            background: #f3f4f6;
            color: #374151;
            border: 1px solid #d1d5db;
        }

        .btn-secondary:hover {
            background: #e5e7eb;
            transform: translateY(-1px);
        }

        .error-illustration {
            margin-bottom: 1.5rem;
        }

        .error-illustration svg {
            width: 80px;
            height: 80px;
            color: #6b7280;
        }

        @media (max-width: 640px) {
            .error-card {
                width: 95%;
                margin: 1rem;
            }

            .error-code {
                font-size: 3rem;
            }

            .error-actions {
                flex-direction: column;
            }

            .btn {
                width: 100%;
                justify-content: center;
            }
        }

        /* Dark mode styles */
        .dark .error-card {
            background: #1f2937;
        }

        .dark .error-description {
            color: #9ca3af;
        }

        .dark .btn-secondary {
            background: #374151;
            color: #d1d5db;
            border-color: #4b5563;
        }

        .dark .btn-secondary:hover {
            background: #4b5563;
        }
    </style>
</head>

<body class="font-sans antialiased">
    <div class="error-container light-gradient-bg">
        <div class="error-card bg-transparent">
            <div class="error-header">
                <h1 class="error-code">@yield('code')</h1>
                <p class="error-message">@yield('message')</p>
            </div>

            <div class="error-body">
                <div class="error-illustration">
                    @yield('illustration')
                </div>

                <div class="error-description">
                    @yield('content')
                </div>

                <div class="error-actions">
                    @yield('actions')
                </div>
            </div>
        </div>
    </div>

    {{-- JavaScript for additional functionality --}}
    <script>
        // Go back functionality
        function goBack() {
            if (window.history.length > 1) {
                window.history.back();
            } else {
                window.location.href = '/';
            }
        }

        // Reload page
        function reloadPage() {
            window.location.reload();
        }

        // Report issue (optional)
        function reportIssue() {
            const subject = encodeURIComponent('Error Report: @yield('code') @yield('message')');
            const body = encodeURIComponent(`I encountered an error on your website:

URL: ${window.location.href}
Error: @yield('code') - @yield('message')
Browser: ${navigator.userAgent}
Time: ${new Date().toISOString()}

Description:
[Please describe what you were trying to do when this error occurred]`);

            window.location.href = `mailto:support@suptix.com?subject=${subject}&body=${body}`;
        }
    </script>
</body>

</html>
