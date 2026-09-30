<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Error {{ $code ?? '???' }} - {{ config('app.name', 'Suptix') }}</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #333;
            margin: 0;
            padding: 20px;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .container {
            text-align: center;
            background: white;
            border-radius: 10px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            max-width: 500px;
            width: 100%;
        }
        .error-code {
            font-size: 4rem;
            font-weight: bold;
            color: #ff6b6b;
            margin: 0;
        }
        .error-message {
            font-size: 1.5rem;
            margin: 10px 0 20px 0;
            color: #555;
        }
        .description {
            color: #666;
            margin-bottom: 30px;
        }
        .button {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 5px;
            margin: 5px;
            transition: transform 0.2s;
        }
        .button:hover {
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="error-code">{{ $code ?? '???' }}</h1>
        <h2 class="error-message">{{ $message ?? 'An error occurred' }}</h2>
        <p class="description">{{ $description ?? 'Something went wrong. Please try again later.' }}</p>
        <a href="{{ url('/') }}" class="button">Go Home</a>
        <a href="javascript:history.back()" class="button">Go Back</a>
    </div>
</body>
</html>