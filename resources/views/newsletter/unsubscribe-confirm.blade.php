<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unsubscribe - Suptix Newsletter</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full bg-white rounded-lg shadow-lg p-8">
        <div class="text-center">
            <h1 class="text-2xl font-bold text-gray-900 mb-4">Unsubscribe from Newsletter</h1>
            
            <p class="text-gray-600 mb-6">
                Are you sure you want to unsubscribe <strong>{{ $newsletter->email }}</strong> from our newsletter?
            </p>
            
            <div class="space-y-4">
                <form method="POST" action="{{ route('newsletter.unsubscribe.confirm', $newsletter->unsubscribe_token) }}">
                    @csrf
                    <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white font-medium py-3 px-4 rounded-lg transition-colors">
                        Yes, Unsubscribe Me
                    </button>
                </form>
                
                <a href="{{ config('app.url') }}" class="block w-full bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-3 px-4 rounded-lg transition-colors text-center">
                    No, Keep Me Subscribed
                </a>
            </div>
            
            <p class="text-sm text-gray-500 mt-6">
                You can resubscribe at any time by visiting our website.
            </p>
        </div>
    </div>
</body>
</html>