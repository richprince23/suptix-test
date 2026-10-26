<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Successfully Unsubscribed - Suptix</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full bg-white rounded-lg shadow-lg p-8">
        <div class="text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100 mb-4">
                <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            
            <h1 class="text-2xl font-bold text-gray-900 mb-4">Successfully Unsubscribed</h1>
            
            <p class="text-gray-600 mb-6">
                <strong>{{ $newsletter->email }}</strong> has been successfully unsubscribed from our newsletter.
            </p>
            
            <p class="text-gray-600 mb-6">
                We're sorry to see you go! We hope you had a great experience with Suptix.
            </p>
            
            <div class="space-y-4">
                <a href="{{ config('app.url') }}" class="block w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-4 rounded-lg transition-colors">
                    Return to Suptix
                </a>
                
                <p class="text-sm text-gray-500">
                    Changed your mind? You can resubscribe at any time by visiting our website and signing up again.
                </p>
            </div>
            
            <div class="mt-8 pt-6 border-t border-gray-200">
                <p class="text-xs text-gray-500">
                    If you have any questions or feedback, please <a href="{{ config('app.url') }}/contact-us" class="text-blue-600 hover:text-blue-700">contact us</a>.
                </p>
            </div>
        </div>
    </div>
</body>
</html>