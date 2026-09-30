<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Event Ticket</title>
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
            border-radius: 0.75rem;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .header {
            background: #8b5cf6;
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
            font-size: 18px;
            opacity: 0.9;
        }
        .content {
            padding: 32px;
        }
        .ticket-info {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border: 2px solid #e5e7eb;
            border-radius: 0.75rem;
            padding: 24px;
            margin-bottom: 24px;
        }
        .qr-section {
            text-align: center;
            margin: 24px 0;
            padding: 20px;
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 0.75rem;
        }
        .details-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-bottom: 24px;
        }
        .detail-item {
            margin-bottom: 12px;
        }
        .detail-label {
            font-weight: bold;
            color: #6b7280;
            font-size: 14px;
        }
        .detail-value {
            color: #374151;
            font-size: 16px;
            margin-top: 4px;
        }
        .cta-button {
            display: inline-block;
            background: #8b5cf6;
            color: white;
            text-decoration: none;
            padding: 12px 24px;
            border-radius: 0.75rem;
            font-weight: bold;
            margin: 16px 0;
        }
        .footer {
            background-color: #f9fafb;
            padding: 24px 32px;
            text-align: center;
            font-size: 14px;
            color: #6b7280;
            border-top: 1px solid #e5e7eb;
        }
        @media (max-width: 600px) {
            .details-grid {
                grid-template-columns: 1fr;
            }
            .container {
                margin: 16px;
                border-radius: 0.75rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>Your Event Ticket</h1>
            <p>{{ $event->title }}</p>
        </div>

        <!-- Content -->
        <div class="content">
            <!-- Greeting -->
            <h2 style="color: #374151; margin-bottom: 16px;">Hello {{ $ticket->attendee_name }}!</h2>
            <p style="margin-bottom: 24px; line-height: 1.6;">
                Your ticket for <strong>{{ $event->title }}</strong> is ready! Please find your ticket details below.
            </p>

            <!-- Ticket Information -->
            <div class="ticket-info">
                <h3 style="margin: 0 0 16px 0; color: #374151;">Ticket Information</h3>

                <div class="details-grid">
                    <div>
                        <div class="detail-item">
                            <div class="detail-label">Ticket Number</div>
                            <div class="detail-value">{{ $ticket->ticket_number }}</div>
                        </div>

                        <div class="detail-item">
                            <div class="detail-label">Attendee Name</div>
                            <div class="detail-value">{{ $ticket->attendee_name }}</div>
                        </div>

                        <div class="detail-item">
                            <div class="detail-label">Ticket Type</div>
                            <div class="detail-value">{{ $ticket->ticketType->name ?? 'Standard' }}</div>
                        </div>
                    </div>

                    <div>
                        <div class="detail-item">
                            <div class="detail-label">Order Number</div>
                            <div class="detail-value">{{ $order->order_number }}</div>
                        </div>

                        <div class="detail-item">
                            <div class="detail-label">Price</div>
                            <div class="detail-value">GHS {{ $ticket->price }}</div>
                        </div>

                        <div class="detail-item">
                            <div class="detail-label">Status</div>
                            <div class="detail-value" style="color: #10b981;">{{ ucfirst($ticket->status) }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Event Details -->
            <div style="margin-bottom: 24px;">
                <h3 style="margin: 0 0 16px 0; color: #374151;">Event Details</h3>

                <div class="detail-item">
                    <div class="detail-label">Date & Time</div>
                    <div class="detail-value">
                        {{ \Carbon\Carbon::parse($event->start_date)->format('l, F j, Y') }} at
                        {{ \Carbon\Carbon::parse($event->start_time)->format('g:i A') }}
                    </div>
                </div>

                @if($event->mode === 'online' || $event->mode === 'hybrid')
                <div class="detail-item">
                    <div class="detail-label">Platform</div>
                    <div class="detail-value">{{ $event->platform_name }}</div>
                </div>

                @if($event->online_link)
                <div class="detail-item">
                    <div class="detail-label">Meeting Link</div>
                    <div class="detail-value">
                        <a href="{{ $event->online_link }}" style="color: #3b82f6; text-decoration: none;">
                            {{ $event->online_link }}
                        </a>
                    </div>
                </div>
                @endif
                @endif

                @if($venue && $venue->name)
                <div class="detail-item">
                    <div class="detail-label">Venue</div>
                    <div class="detail-value">{{ $venue->name }}</div>
                </div>
                @if($venue->address)
                <div class="detail-item">
                    <div class="detail-label">Location</div>
                    <div class="detail-value">{{ $venue->address }}</div>
                </div>
                @endif
                @endif

                @if($organizer)
                <div class="detail-item">
                    <div class="detail-label">Organizer</div>
                    <div class="detail-value">{{ $organizer->name }}</div>
                </div>
                @endif
            </div>

            <!-- QR Code Section -->
            <div class="qr-section">
                <h4 style="margin: 0 0 12px 0; color: #374151;">Digital Ticket</h4>
                <p style="margin: 0 0 16px 0; color: #6b7280; font-size: 14px;">
                    Present this QR code at the event entrance for quick check-in
                </p>

                <!-- QR Code will be generated dynamically -->
                <div style="margin: 16px 0;">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data={{ $ticket->ticket_number }}"
                         alt="Ticket QR Code"
                         style="width: 200px; height: 200px; border: 1px solid #e5e7eb; border-radius: 0.75rem;">
                </div>

                <p style="margin: 8px 0 0 0; color: #6b7280; font-size: 12px;">
                    Ticket ID: {{ $ticket->ticket_number }}
                </p>
            </div>

            <!-- Call to Action -->
            <div style="text-align: center; margin: 24px 0;">
                <a href="{{ $ticketDetailsUrl }}" class="cta-button">
                    View & Download Ticket
                </a>
            </div>

            <!-- Important Notes -->
            <div style="background: #fef3cd; border: 1px solid #f59e0b; border-radius: 0.75rem; padding: 16px; margin-top: 24px;">
                <h4 style="margin: 0 0 8px 0; color: #92400e;">Important Notes:</h4>
                <ul style="margin: 0; padding-left: 20px; color: #92400e;">
                    <li>Please arrive at least 30 minutes before the event starts</li>
                    <li>Bring a valid ID that matches the attendee name on this ticket</li>
                    <li>This ticket is non-transferable and non-refundable</li>
                    <li>Keep this email safe as it contains your entry information</li>
                </ul>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p style="margin: 0 0 8px 0;">
                <strong>Suptix</strong> - Your trusted event ticketing platform
            </p>
            <p style="margin: 0;">
                If you have any questions, please contact the event organizer or visit our support center.
            </p>
        </div>
    </div>
</body>
</html>
