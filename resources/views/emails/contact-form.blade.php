<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Contact Form Submission</title>
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
        .info-section {
            background-color: #f9fafb;
            border-radius: 8px;
            padding: 24px;
            margin-bottom: 24px;
        }
        .info-row {
            display: flex;
            margin-bottom: 12px;
        }
        .info-row:last-child {
            margin-bottom: 0;
        }
        .info-label {
            font-weight: bold;
            width: 120px;
            color: #6b7280;
        }
        .info-value {
            flex: 1;
            color: #111827;
        }
        .message-section {
            background-color: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 16px;
            margin: 24px 0;
        }
        .message-section h3 {
            margin: 0 0 8px 0;
            color: #92400e;
        }
        .urgent {
            background-color: #fef2f2;
            border-left: 4px solid #ef4444;
        }
        .urgent h3 {
            color: #dc2626;
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
        .priority-high {
            background-color: #fef2f2;
            color: #dc2626;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .priority-medium {
            background-color: #fef3c7;
            color: #92400e;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📧 New Contact Form Submission</h1>
            <p>A new message has been received through the Suptix contact form</p>
        </div>
        
        <div class="content">
            <div class="info-section">
                <h2 style="margin-top: 0; color: #111827;">Contact Information</h2>
                
                <div class="info-row">
                    <div class="info-label">Name:</div>
                    <div class="info-value">{{ $contactData['firstName'] }} {{ $contactData['lastName'] }}</div>
                </div>
                
                <div class="info-row">
                    <div class="info-label">Email:</div>
                    <div class="info-value">
                        <a href="mailto:{{ $contactData['email'] }}" style="color: #3b82f6;">{{ $contactData['email'] }}</a>
                    </div>
                </div>
                
                @if(!empty($contactData['phone']))
                <div class="info-row">
                    <div class="info-label">Phone:</div>
                    <div class="info-value">
                        <a href="tel:{{ $contactData['phone'] }}" style="color: #3b82f6;">{{ $contactData['phone'] }}</a>
                    </div>
                </div>
                @endif
                
                <div class="info-row">
                    <div class="info-label">Subject:</div>
                    <div class="info-value">
                        <strong>{{ $contactData['subject'] }}</strong>
                        @if(in_array($contactData['subject'], ['support', 'billing', 'refund']))
                            <span class="priority-high">High Priority</span>
                        @elseif(in_array($contactData['subject'], ['organizer', 'partnership']))
                            <span class="priority-medium">Medium Priority</span>
                        @endif
                    </div>
                </div>
                
                <div class="info-row">
                    <div class="info-label">Submitted:</div>
                    <div class="info-value">{{ now()->format('F j, Y \a\t g:i A T') }}</div>
                </div>
                
                @if(isset($contactData['newsletter']) && $contactData['newsletter'])
                <div class="info-row">
                    <div class="info-label">Newsletter:</div>
                    <div class="info-value">✅ Subscribed to newsletter</div>
                </div>
                @endif
            </div>
            
            <div class="message-section @if(in_array($contactData['subject'], ['support', 'billing', 'refund'])) urgent @endif">
                <h3>Message:</h3>
                <p style="margin: 0; line-height: 1.6;">{{ $contactData['message'] }}</p>
            </div>
            
            <div style="background-color: #eff6ff; padding: 16px; border-radius: 8px; margin-top: 24px;">
                <h3 style="margin: 0 0 8px 0; color: #1e40af;">📞 Quick Actions</h3>
                <p style="margin: 0; font-size: 14px; color: #1e40af;">
                    <a href="mailto:{{ $contactData['email'] }}?subject=Re: {{ $contactData['subject'] }}" style="color: #1e40af;">Reply to {{ $contactData['firstName'] }}</a> | 
                    <a href="tel:{{ $contactData['phone'] ?? '' }}" style="color: #1e40af;">Call Now</a>
                </p>
            </div>
        </div>
        
        <div class="footer">
            <p>This email was automatically generated from the Suptix contact form.</p>
            <p>
                <a href="{{ config('app.url') }}">Visit Suptix</a> | 
                <a href="{{ config('app.url') }}/contact-us">Contact Form</a>
            </p>
        </div>
    </div>
</body>
</html>