<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thank you for contacting Suptix</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background-color: #f8fafc;
            color: #374151;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 0px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .header {
            background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%);
            color: white;
            padding: 32px;
            text-align: center;
        }
        .header h1 {
            margin: 0 0 8px 0;
            font-size: 28px;
            font-weight: bold;
        }
        .header p {
            margin: 0;
            font-size: 16px;
            opacity: 0.9;
        }
        .content {
            padding: 32px;
        }
        .highlight-box {
            background-color: #eff6ff;
            border-left: 4px solid #3b82f6;
            padding: 20px;
            margin: 24px 0;
            border-radius: 0 8px 8px 0;
        }
        .info-section {
            background-color: #f9fafb;
            border-radius: 8px;
            padding: 24px;
            margin: 24px 0;
        }
        .contact-info {
            background-color: #f0fdf4;
            border-radius: 8px;
            padding: 20px;
            margin: 24px 0;
        }
        .contact-info h3 {
            margin: 0 0 16px 0;
            color: #065f46;
        }
        .contact-row {
            display: flex;
            align-items: center;
            margin-bottom: 12px;
        }
        .contact-row:last-child {
            margin-bottom: 0;
        }
        .contact-icon {
            width: 20px;
            height: 20px;
            margin-right: 12px;
            opacity: 0.7;
        }
        .footer {
            background-color: #f9fafb;
            padding: 24px;
            text-align: center;
            font-size: 14px;
            color: #6b7280;
        }
        .footer a {
            color: #3b82f6;
            text-decoration: none;
        }
        .btn {
            display: inline-block;
            background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%);
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
            margin: 16px 0;
        }
        .social-links {
            margin: 20px 0;
        }
        .social-links a {
            display: inline-block;
            margin: 0 8px;
            color: #6b7280;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>✉️ Thank You!</h1>
            <p>We've received your message and will get back to you soon</p>
        </div>

        <div class="content">
            <p>Hi {{ $contactData['firstName'] }},</p>

            <p>Thank you for reaching out to us! We've received your message regarding "<strong>{{ $contactData['subject'] }}</strong>" and our team will review it carefully.</p>

            <div class="highlight-box">
                <h3 style="margin: 0 0 8px 0; color: #1e40af;">📋 What happens next?</h3>
                <ul style="margin: 0; padding-left: 20px; color: #1e40af;">
                    <li>Our team will review your message within 24 hours</li>
                    <li>You'll receive a detailed response from one of our specialists</li>
                    <li>For urgent matters, we may call you directly</li>
                </ul>
            </div>

            <div class="info-section">
                <h3 style="margin: 0 0 16px 0; color: #111827;">📄 Your Message Summary</h3>
                <p><strong>Name:</strong> {{ $contactData['firstName'] }} {{ $contactData['lastName'] }}</p>
                <p><strong>Email:</strong> {{ $contactData['email'] }}</p>
                <p><strong>Subject:</strong> {{ $contactData['subject'] }}</p>
                <p><strong>Submitted:</strong> {{ now()->format('F j, Y \a\t g:i A') }}</p>
            </div>

            @if(in_array($contactData['subject'], ['support', 'billing', 'refund']))
            <div style="background-color: #fef2f2; border-left: 4px solid #ef4444; padding: 16px; margin: 24px 0; border-radius: 0 8px 8px 0;">
                <h3 style="margin: 0 0 8px 0; color: #dc2626;">🚨 Priority Support</h3>
                <p style="margin: 0; color: #dc2626;">Your request has been marked as high priority. Our support team will respond within 4-6 hours during business hours.</p>
            </div>
            @endif

            <div class="contact-info">
                <h3>Need immediate assistance?</h3>

                <div class="contact-row">
                    <span style="font-weight: bold; color: #065f46;">📞 Phone:</span>
                    <span style="margin-left: 12px;">+233242728115</span>
                </div>

                <div class="contact-row">
                    <span style="font-weight: bold; color: #065f46;">📧 Email:</span>
                    <span style="margin-left: 12px;">support@suptix.live</span>
                </div>

                <div class="contact-row">
                    <span style="font-weight: bold; color: #065f46;">💬 WhatsApp:</span>
                    <span style="margin-left: 12px;"><a href="https://wa.me/233245001476" style="color: #059669; text-decoration: none;">+233245001476</a></span>
                </div>

                <div class="contact-row">
                    <span style="font-weight: bold; color: #065f46;">⏰ Hours:</span>
                    <span style="margin-left: 12px;">Monday - Friday, 9:00 AM - 6:00 PM GMT</span>
                </div>
            </div>

            <div style="text-align: center; margin: 32px 0;">
                <p>While you wait, explore what Suptix can do for your events:</p>
                <a href="{{ config('app.url') }}/events" class="btn">Browse Events</a>
                <a href="{{ config('app.url') }}/request-demo" class="btn">Request Demo</a>
            </div>

            @if(isset($contactData['newsletter']) && $contactData['newsletter'])
            <div style="background-color: #f0f9ff; border-radius: 8px; padding: 16px; margin: 24px 0;">
                <p style="margin: 0; color: #0369a1;">
                    🎉 <strong>Newsletter Subscription Confirmed!</strong> You'll receive our latest updates, event tips, and industry insights.
                </p>
            </div>
            @endif
        </div>

        <div class="footer">
            <p><strong>Thank you for choosing Suptix!</strong></p>
            <p>Your trusted partner for event ticketing and management.</p>

            <div class="social-links">
                <a href="#">Facebook</a> |
                <a href="#">Twitter</a> |
                <a href="#">LinkedIn</a> |
                <a href="#">Instagram</a>
            </div>

            <p>
                <a href="{{ config('app.url') }}">Visit Suptix</a> |
                <a href="{{ config('app.url') }}/help-center">Help Center</a> |
                <a href="{{ config('app.url') }}/contact-us">Contact Us</a>
            </p>

            <p style="font-size: 12px; color: #9ca3af; margin-top: 16px;">
                This email was sent to {{ $contactData['email'] }}.
                If you didn't submit this form, please ignore this email.
            </p>
        </div>
    </div>
</body>
</html>
