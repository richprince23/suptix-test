<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Demo Request Confirmation - Suptix</title>
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
        .timeline-section {
            background-color: #f0fdf4;
            border-radius: 8px;
            padding: 20px;
            margin: 24px 0;
        }
        .urgent-timeline {
            background-color: #fef2f2;
        }
        .steps-list {
            background-color: #eff6ff;
            border-radius: 8px;
            padding: 24px;
            margin: 24px 0;
        }
        .steps-list h3 {
            margin: 0 0 16px 0;
            color: #1e40af;
        }
        .step {
            display: flex;
            align-items: flex-start;
            margin-bottom: 16px;
        }
        .step:last-child {
            margin-bottom: 0;
        }
        .step-number {
            background-color: #3b82f6;
            color: white;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: bold;
            margin-right: 12px;
            flex-shrink: 0;
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
        .feature-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin: 24px 0;
        }
        .feature-item {
            background-color: #f9fafb;
            border-radius: 8px;
            padding: 16px;
            text-align: center;
        }
        .feature-icon {
            font-size: 24px;
            margin-bottom: 8px;
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
            margin: 16px 8px;
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
        .calendar-integration {
            background-color: #fef3c7;
            border-radius: 8px;
            padding: 20px;
            margin: 24px 0;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Demo Request Received!</h1>
            <p>We're excited to show you what Suptix can do for {{ $demoData['company'] }}</p>
        </div>

        <div class="content">
            <p>Hi {{ $demoData['firstName'] }},</p>

            <p>Thank you for requesting a demo of Suptix! We're thrilled that you're considering our platform to streamline your event management process.</p>

            @php
                $responseTime = '';
                $timelineClass = '';
                switch($demoData['timeline']) {
                    case 'immediate':
                        $responseTime = 'within 2-4 hours';
                        $timelineClass = 'urgent-timeline';
                        break;
                    case '1-3months':
                        $responseTime = 'within 24 hours';
                        break;
                    default:
                        $responseTime = 'within 24-48 hours';
                }
            @endphp

            <div class="timeline-section {{ $timelineClass }}">
                <h3 style="margin: 0 0 8px 0; color: #065f46;">What happens next?</h3>
                <p style="margin: 0; color: #065f46;">
                    <strong>Our sales team will contact you {{ $responseTime }}</strong>
                    @if($demoData['timeline'] === 'immediate')
                        <br><em>We understand you need a solution quickly - we'll prioritize your request!</em>
                    @endif
                </p>
            </div>

            <div class="steps-list">
                <h3>Demo Process</h3>
                <div class="step">
                    <div class="step-number">1</div>
                    <div>
                        <strong>Initial Contact</strong><br>
                        Our specialist will reach out to schedule your personalized demo
                    </div>
                </div>
                <div class="step">
                    <div class="step-number">2</div>
                    <div>
                        <strong>Customized Demo (30-45 minutes)</strong><br>
                        We'll focus on your specific needs: {{ implode(', ', array_slice($demoData['eventTypes'], 0, 3)) }}
                        @if(count($demoData['eventTypes']) > 3) and more @endif
                    </div>
                </div>
                <div class="step">
                    <div class="step-number">3</div>
                    <div>
                        <strong>Q&A Session</strong><br>
                        Get all your questions answered about features, pricing, and implementation
                    </div>
                </div>
                <div class="step">
                    <div class="step-number">4</div>
                    <div>
                        <strong>Custom Proposal</strong><br>
                        Receive a tailored solution for {{ $demoData['company'] }}'s needs
                    </div>
                </div>
            </div>

            <div class="info-section">
                <h3 style="margin: 0 0 16px 0; color: #111827;">Your Demo Request Summary</h3>
                <p><strong>Company:</strong> {{ $demoData['company'] }} ({{ $demoData['companySize'] }})</p>
                <p><strong>Contact:</strong> {{ $demoData['firstName'] }} {{ $demoData['lastName'] }}, {{ $demoData['jobTitle'] }}</p>
                <p><strong>Event Volume:</strong> {{ $demoData['eventsPerYear'] }} with {{ $demoData['averageAttendees'] }} average attendees</p>
                <p><strong>Timeline:</strong> {{ ucfirst(str_replace('-', ' ', $demoData['timeline'])) }}</p>
                @if(!empty($demoData['preferredDate']))
                <p><strong>Preferred Date:</strong> {{ \Carbon\Carbon::parse($demoData['preferredDate'])->format('F j, Y') }}
                @if(!empty($demoData['preferredTime'])) ({{ ucfirst($demoData['preferredTime']) }}) @endif</p>
                @endif
            </div>

            <div class="highlight-box">
                <h3 style="margin: 0 0 12px 0; color: #1e40af;">Demo Focus Areas</h3>
                <p style="margin: 0; color: #1e40af; font-style: italic;">"{{ $demoData['demoFocus'] }}"</p>
            </div>

            <div class="feature-grid">
                <div class="feature-item">
                    <div class="feature-icon">🎫</div>
                    <strong>Ticket Management</strong>
                    <p style="font-size: 14px; margin: 8px 0 0 0;">Multiple ticket types, pricing tiers, and availability controls</p>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">📊</div>
                    <strong>Analytics & Reports</strong>
                    <p style="font-size: 14px; margin: 8px 0 0 0;">Real-time sales data and attendee insights</p>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">💳</div>
                    <strong>Payment Processing</strong>
                    <p style="font-size: 14px; margin: 8px 0 0 0;">Secure, fast checkout with multiple payment options</p>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">📱</div>
                    <strong>Mobile Experience</strong>
                    <p style="font-size: 14px; margin: 8px 0 0 0;">Responsive design and mobile check-in capabilities</p>
                </div>
            </div>

            @if(!empty($demoData['currentSolution']))
            <div style="background-color: #fef3c7; border-radius: 8px; padding: 16px; margin: 24px 0;">
                <h4 style="margin: 0 0 8px 0; color: #92400e;">Migration Support</h4>
                <p style="margin: 0; color: #92400e;">
                    We'll show you how to seamlessly transition from {{ $demoData['currentSolution'] }} to Suptix, including data migration and setup assistance.
                </p>
            </div>
            @endif

            <div class="calendar-integration">
                <h3 style="margin: 0 0 12px 0; color: #92400e;">Schedule Directly</h3>
                <p style="margin: 0 0 16px 0; color: #92400e;">Can't wait for our call? Book your demo slot directly:</p>
                <a href="https://calendly.com/suptix-demo" class="btn">Book Demo Now</a>
            </div>

            <div class="contact-info">
                <h3>Need immediate assistance?</h3>

                <div class="contact-row">
                    <span style="font-weight: bold; color: #065f46;">Sales Direct:</span>
                    <span style="margin-left: 12px;">+233242728115</span>
                </div>

                <div class="contact-row">
                    <span style="font-weight: bold; color: #065f46;">Sales Email:</span>
                    <span style="margin-left: 12px;">hello@suptix.live</span>
                </div>

                <div class="contact-row">
                    <span style="font-weight: bold; color: #065f46;">Hours:</span>
                    <span style="margin-left: 12px;">Monday - Friday, 9:00 AM - 6:00 PM EST</span>
                </div>
            </div>

            <div style="text-align: center; margin: 32px 0;">
                <p>While you wait, explore our platform:</p>
                <a href="{{ config('app.url') }}/events" class="btn">Browse Events</a>
                <a href="{{ config('app.url') }}/help-center" class="btn">Help Center</a>
            </div>

            @if(isset($demoData['newsletter']) && $demoData['newsletter'])
            <div style="background-color: #f0f9ff; border-radius: 8px; padding: 16px; margin: 24px 0;">
                <p style="margin: 0; color: #0369a1;">
                    <strong>Newsletter Subscription Confirmed!</strong> You'll receive our latest platform updates, event management tips, and industry insights.
                </p>
            </div>
            @endif
        </div>

        <div class="footer">
            <p><strong>Thank you for considering Suptix!</strong></p>
            <p>We're excited to show you how we can transform your event management process.</p>

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
                This email was sent to {{ $demoData['email'] }}.
                If you didn't request this demo, please ignore this email.
            </p>
        </div>
    </div>
</body>
</html>
