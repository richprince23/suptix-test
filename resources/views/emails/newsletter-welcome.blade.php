<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Suptix Newsletter</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px 20px;
            text-align: center;
            border-radius: 10px 10px 0 0;
        }
        .content {
            background: #f8f9fa;
            padding: 30px 20px;
            border-radius: 0 0 10px 10px;
        }
        .button {
            display: inline-block;
            background: #667eea;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            font-size: 12px;
            color: #666;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Welcome to Suptix!</h1>
        <p>Thank you for subscribing to our newsletter</p>
    </div>
    
    <div class="content">
        <p>Hi there!</p>
        
        <p>Welcome to the Suptix community! You've successfully subscribed to our newsletter and we're excited to have you on board.</p>
        
        <p>Here's what you can expect from us:</p>
        <ul>
            <li><strong>Latest Event Updates:</strong> Stay informed about the hottest events happening around you</li>
            <li><strong>Platform Features:</strong> Be the first to know about new features and improvements</li>
            <li><strong>Industry Insights:</strong> Get valuable tips and trends in event management</li>
            <li><strong>Exclusive Offers:</strong> Access special discounts and early-bird offers</li>
        </ul>
        
        <p>We respect your inbox and promise to only send you valuable content. You can manage your subscription preferences or unsubscribe at any time.</p>
        
        <p>Ready to discover amazing events? Start exploring!</p>
        
        <a href="{{ config('app.url') }}" class="button">Explore Events</a>
        
        <p>Thanks again for joining us!</p>
        <p>The Suptix Team</p>
    </div>
    
    <div class="footer">
        <p>You're receiving this email because you subscribed to our newsletter at {{ config('app.url') }}</p>
        <p>
            <a href="{{ $newsletter->unsubscribe_url }}">Unsubscribe</a> | 
            <a href="{{ config('app.url') }}/privacy-policy">Privacy Policy</a> | 
            <a href="{{ config('app.url') }}/contact-us">Contact Us</a>
        </p>
        <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
    </div>
</body>
</html>