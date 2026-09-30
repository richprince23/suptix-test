<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Demo Request</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background-color: #f8fafc;
            color: #374151;
        }
        .container {
            max-width: 700px;
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
        .priority-section {
            background-color: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 20px;
            margin-bottom: 24px;
            border-radius: 0 8px 8px 0;
        }
        .urgent-priority {
            background-color: #fef2f2;
            border-left-color: #ef4444;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
            margin-bottom: 24px;
        }
        .info-section {
            background-color: #f9fafb;
            border-radius: 8px;
            padding: 20px;
        }
        .info-section h3 {
            margin: 0 0 16px 0;
            color: #111827;
            font-size: 16px;
        }
        .info-row {
            margin-bottom: 8px;
        }
        .info-row:last-child {
            margin-bottom: 0;
        }
        .label {
            font-weight: bold;
            color: #6b7280;
            display: inline-block;
            width: 120px;
        }
        .value {
            color: #111827;
        }
        .event-types {
            background-color: #eff6ff;
            border-radius: 8px;
            padding: 16px;
            margin: 16px 0;
        }
        .event-types h4 {
            margin: 0 0 8px 0;
            color: #1e40af;
        }
        .event-type-list {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }
        .event-type-tag {
            background-color: #3b82f6;
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
        }
        .demo-focus {
            background-color: #f0fdf4;
            border-radius: 8px;
            padding: 20px;
            margin: 24px 0;
        }
        .demo-focus h3 {
            margin: 0 0 12px 0;
            color: #065f46;
        }
        .action-section {
            background-color: #eff6ff;
            border-radius: 8px;
            padding: 24px;
            margin: 24px 0;
            text-align: center;
        }
        .btn {
            display: inline-block;
            background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%);
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
            margin: 8px;
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
        .timeline-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .timeline-immediate {
            background-color: #fef2f2;
            color: #dc2626;
        }
        .timeline-short {
            background-color: #fef3c7;
            color: #92400e;
        }
        .timeline-medium {
            background-color: #eff6ff;
            color: #1d4ed8;
        }
        .timeline-long {
            background-color: #f0fdf4;
            color: #166534;
        }
        @media (max-width: 600px) {
            .info-grid {
                grid-template-columns: 1fr;
                gap: 16px;
            }
            .content {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🚀 New Demo Request</h1>
            <p>High-quality lead from {{ $demoData['company'] }}</p>
        </div>
        
        <div class="content">
            @php
                $timelinePriority = '';
                $timelineClass = '';
                switch($demoData['timeline']) {
                    case 'immediate':
                        $timelinePriority = 'URGENT - IMMEDIATE';
                        $timelineClass = 'urgent-priority timeline-immediate';
                        break;
                    case '1-3months':
                        $timelinePriority = 'HIGH PRIORITY';
                        $timelineClass = 'timeline-short';
                        break;
                    case '3-6months':
                        $timelinePriority = 'MEDIUM PRIORITY';
                        $timelineClass = 'timeline-medium';
                        break;
                    default:
                        $timelinePriority = 'STANDARD';
                        $timelineClass = 'timeline-long';
                }
            @endphp
            
            <div class="priority-section {{ $timelineClass }}">
                <h3 style="margin: 0 0 8px 0;">⚡ {{ $timelinePriority }}</h3>
                <p style="margin: 0;">
                    <strong>Timeline:</strong> {{ ucfirst(str_replace('-', ' ', $demoData['timeline'])) }}
                    @if($demoData['timeline'] === 'immediate')
                        - Contact within 2 hours!
                    @elseif($demoData['timeline'] === '1-3months')
                        - Contact within 24 hours
                    @endif
                </p>
            </div>
            
            <div class="info-grid">
                <div class="info-section">
                    <h3>👤 Contact Information</h3>
                    <div class="info-row">
                        <span class="label">Name:</span>
                        <span class="value">{{ $demoData['firstName'] }} {{ $demoData['lastName'] }}</span>
                    </div>
                    <div class="info-row">
                        <span class="label">Email:</span>
                        <span class="value">
                            <a href="mailto:{{ $demoData['email'] }}" style="color: #3b82f6;">{{ $demoData['email'] }}</a>
                        </span>
                    </div>
                    <div class="info-row">
                        <span class="label">Phone:</span>
                        <span class="value">
                            <a href="tel:{{ $demoData['phone'] }}" style="color: #3b82f6;">{{ $demoData['phone'] }}</a>
                        </span>
                    </div>
                    <div class="info-row">
                        <span class="label">Job Title:</span>
                        <span class="value">{{ $demoData['jobTitle'] }}</span>
                    </div>
                </div>
                
                <div class="info-section">
                    <h3>🏢 Company Details</h3>
                    <div class="info-row">
                        <span class="label">Company:</span>
                        <span class="value"><strong>{{ $demoData['company'] }}</strong></span>
                    </div>
                    <div class="info-row">
                        <span class="label">Size:</span>
                        <span class="value">{{ $demoData['companySize'] }} employees</span>
                    </div>
                    <div class="info-row">
                        <span class="label">Events/Year:</span>
                        <span class="value">{{ $demoData['eventsPerYear'] }}</span>
                    </div>
                    <div class="info-row">
                        <span class="label">Avg Attendees:</span>
                        <span class="value">{{ $demoData['averageAttendees'] }}</span>
                    </div>
                    @if(!empty($demoData['currentSolution']))
                    <div class="info-row">
                        <span class="label">Current Tool:</span>
                        <span class="value">{{ $demoData['currentSolution'] }}</span>
                    </div>
                    @endif
                </div>
            </div>
            
            <div class="event-types">
                <h4>🎯 Event Types They Organize:</h4>
                <div class="event-type-list">
                    @foreach($demoData['eventTypes'] as $eventType)
                        <span class="event-type-tag">{{ ucfirst(str_replace('-', ' ', $eventType)) }}</span>
                    @endforeach
                </div>
            </div>
            
            <div class="demo-focus">
                <h3>💡 Demo Focus & Requirements</h3>
                <p style="margin: 0; line-height: 1.6;">{{ $demoData['demoFocus'] }}</p>
            </div>
            
            @if(!empty($demoData['preferredDate']) || !empty($demoData['preferredTime']))
            <div class="info-section">
                <h3>📅 Scheduling Preferences</h3>
                @if(!empty($demoData['preferredDate']))
                <div class="info-row">
                    <span class="label">Preferred Date:</span>
                    <span class="value">{{ \Carbon\Carbon::parse($demoData['preferredDate'])->format('F j, Y') }}</span>
                </div>
                @endif
                @if(!empty($demoData['preferredTime']))
                <div class="info-row">
                    <span class="label">Preferred Time:</span>
                    <span class="value">{{ ucfirst($demoData['preferredTime']) }}</span>
                </div>
                @endif
            </div>
            @endif
            
            <div class="action-section">
                <h3 style="margin: 0 0 16px 0; color: #1e40af;">🎯 Quick Actions</h3>
                <a href="mailto:{{ $demoData['email'] }}?subject=Demo Scheduling - {{ $demoData['company'] }}&body=Hi {{ $demoData['firstName'] }},%0A%0AThank you for your interest in Suptix! I'd love to schedule a personalized demo for {{ $demoData['company'] }}.%0A%0ABased on your requirements, I think you'll be particularly interested in our features for {{ implode(', ', array_slice($demoData['eventTypes'], 0, 3)) }}.%0A%0AWhen would be a good time for a 30-45 minute demo?%0A%0ABest regards," class="btn">📧 Reply to Lead</a>
                <a href="tel:{{ $demoData['phone'] }}" class="btn">📞 Call Now</a>
                
                @if(!empty($demoData['preferredDate']))
                <p style="margin: 16px 0 0 0; color: #6b7280; font-size: 14px;">
                    💡 They prefer: {{ \Carbon\Carbon::parse($demoData['preferredDate'])->format('F j, Y') }}
                    @if(!empty($demoData['preferredTime'])) - {{ ucfirst($demoData['preferredTime']) }} @endif
                </p>
                @endif
            </div>
            
            <div style="background-color: #f9fafb; border-radius: 8px; padding: 20px; margin: 24px 0;">
                <h3 style="margin: 0 0 12px 0; color: #111827;">📊 Lead Scoring</h3>
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span>Quality Score:</span>
                    <span style="font-weight: bold; color: #059669;">
                        @php
                            $score = 0;
                            // Company size scoring
                            if(in_array($demoData['companySize'], ['201-500', '501-1000', '1000+'])) $score += 3;
                            elseif(in_array($demoData['companySize'], ['51-200'])) $score += 2;
                            else $score += 1;
                            
                            // Events per year scoring
                            if(in_array($demoData['eventsPerYear'], ['26-50', '50+'])) $score += 3;
                            elseif(in_array($demoData['eventsPerYear'], ['11-25'])) $score += 2;
                            else $score += 1;
                            
                            // Timeline scoring
                            if($demoData['timeline'] === 'immediate') $score += 4;
                            elseif($demoData['timeline'] === '1-3months') $score += 3;
                            elseif($demoData['timeline'] === '3-6months') $score += 2;
                            else $score += 1;
                            
                            // Bonus for multiple event types
                            if(count($demoData['eventTypes']) >= 3) $score += 1;
                            
                            $maxScore = 11;
                            $percentage = round(($score / $maxScore) * 100);
                        @endphp
                        {{ $score }}/{{ $maxScore }} ({{ $percentage }}%)
                        @if($percentage >= 80) 🔥 HOT LEAD
                        @elseif($percentage >= 60) ⭐ QUALIFIED
                        @else 👀 POTENTIAL
                        @endif
                    </span>
                </div>
            </div>
            
            @if(isset($demoData['newsletter']) && $demoData['newsletter'])
            <div style="background-color: #f0f9ff; border-radius: 8px; padding: 16px; margin: 24px 0;">
                <p style="margin: 0; color: #0369a1;">
                    📧 <strong>Marketing Consent:</strong> Subscribed to newsletter updates
                </p>
            </div>
            @endif
        </div>
        
        <div class="footer">
            <p><strong>Demo Request submitted:</strong> {{ now()->format('F j, Y \a\t g:i A T') }}</p>
            <p>
                <a href="{{ config('app.url') }}">Visit Suptix</a> | 
                <a href="{{ config('app.url') }}/request-demo">Demo Page</a>
            </p>
            <p style="font-size: 12px; color: #9ca3af; margin-top: 16px;">
                This email was automatically generated from the Suptix demo request form.
            </p>
        </div>
    </div>
</body>
</html>