<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Ticket - {{ $ticket->ticket_number }}</title>
    <style>
        body {
            margin: 0;
            padding: 20px;
            font-family: Arial, sans-serif;
            background-color: #ffffff;
            color: #374151;
            font-size: 14px;
        }
        .ticket {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border: 2px solid #e5e7eb;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .header {
            background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%);
            color: white;
            padding: 24px;
            text-align: center;
        }
        .header h1 {
            margin: 0 0 8px 0;
            font-size: 24px;
            font-weight: bold;
        }
        .header p {
            margin: 0;
            font-size: 16px;
            opacity: 0.9;
        }
        .content {
            padding: 24px;
        }
        .section {
            margin-bottom: 24px;
        }
        .section-title {
            font-size: 18px;
            font-weight: bold;
            color: #374151;
            margin-bottom: 12px;
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 8px;
        }
        .details-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-bottom: 16px;
        }
        .detail-item {
            margin-bottom: 12px;
        }
        .detail-label {
            font-weight: bold;
            color: #6b7280;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .detail-value {
            color: #374151;
            font-size: 14px;
            margin-top: 4px;
            font-weight: 500;
        }
        .qr-section {
            text-align: center;
            padding: 20px;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            margin: 16px 0;
        }
        .status {
            display: inline-block;
            padding: 4px 12px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-active {
            background-color: #d1fae5;
            color: #065f46;
        }
        .status-used {
            background-color: #dbeafe;
            color: #1e40af;
        }
        .status-cancelled {
            background-color: #fee2e2;
            color: #991b1b;
        }
        .ticket-number {
            font-family: 'Courier New', monospace;
            font-size: 16px;
            font-weight: bold;
            background: #f3f4f6;
            padding: 8px 16px;
            display: inline-block;
            margin: 8px 0;
        }
        .footer {
            background-color: #f9fafb;
            padding: 16px 24px;
            text-align: center;
            font-size: 12px;
            color: #6b7280;
            border-top: 1px solid #e5e7eb;
        }
        .important-notes {
            background: #fef3cd;
            border: 1px solid #f59e0b;

            padding: 16px;
            margin-top: 16px;
        }
        .important-notes h4 {
            margin: 0 0 8px 0;
            color: #92400e;
            font-size: 14px;
        }
        .important-notes ul {
            margin: 0;
            padding-left: 16px;
            color: #92400e;
            font-size: 12px;
        }
        .important-notes li {
            margin-bottom: 4px;
        }
        @media print {
            body {
                margin: 0;
                padding: 0;
            }
            .ticket {
                box-shadow: none;
                border: 1px solid #000;
                border-radius: 0.75rem
            }
        }
    </style>
</head>
<body>
    <div class="ticket">
        <!-- Header -->
        <div class="header">
            <h1>Event Ticket</h1>
            <p>{{ $ticket->event->title }}</p>
        </div>

        <!-- Content -->
        <div class="content">
            <!-- Ticket Number -->
            <div class="section" style="text-align: center;">
                <div class="section-title">Ticket Number</div>
                <div class="ticket-number">{{ $ticket->ticket_number }}</div>
            </div>
 <!-- QR Code Section -->
 <div class="qr-section">
    <h4 style="margin: 0 0 12px 0; color: #374151;">Digital Verification</h4>
    <p style="margin: 0 0 16px 0; color: #6b7280; font-size: 12px;">
        Present this QR code at the event entrance for quick check-in
    </p>

    @if($qrCodeBase64)
        <!-- QR Code Image -->
        <div style="margin: 16px 0; text-align: center;">
            <img src="{{ $qrCodeBase64 }}" alt="Ticket QR Code" style="width: 150px; height: 150px; border: 1px solid #e5e7eb;">
        </div>
    @else
        <!-- QR Code fallback -->
        <div style="margin: 16px 0; min-height: 150px; display: flex; align-items: center; justify-content: center; border: 2px dashed #d1d5db;">
            <div style="text-align: center; color: #6b7280;">
                <div style="font-size: 48px; margin-bottom: 8px;">⬜</div>
                <div style="font-size: 12px;">QR Code</div>
                <div style="font-size: 10px; margin-top: 4px;">{{ $ticket->ticket_number }}</div>
            </div>
        </div>
    @endif
</div>
            <!-- Attendee Information -->
            <div class="section">
                <div class="section-title">Attendee Information</div>
                <div class="details-grid">
                    <div>
                        <div class="detail-item">
                            <div class="detail-label">Full Name</div>
                            <div class="detail-value">{{ $ticket->attendee_name }}</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Email Address</div>
                            <div class="detail-value">{{ $ticket->attendee_email }}</div>
                        </div>
                    </div>
                    <div>
                        <div class="detail-item">
                            <div class="detail-label">Ticket Type</div>
                            <div class="detail-value">{{ $ticket->ticketType->name ?? 'Standard' }}</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Price</div>
                            <div class="detail-value">GHS {{ number_format($ticketPrice, 2) }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Event Information -->
            <div class="section">
                <div class="section-title">Event Details</div>
                <div class="details-grid">
                    <div>
                        <div class="detail-item">
                            <div class="detail-label">Event Name</div>
                            <div class="detail-value">{{ $ticket->event->title }}</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Date</div>
                            <div class="detail-value">
                                {{ \Carbon\Carbon::parse($ticket->event->start_date)->format('l, F j, Y') }}
                            </div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Time</div>
                            <div class="detail-value">
                                {{ \Carbon\Carbon::parse($ticket->event->start_time)->format('g:i A') }}
                                @if($ticket->event->end_time)
                                    - {{ \Carbon\Carbon::parse($ticket->event->end_time)->format('g:i A') }}
                                @endif
                            </div>
                        </div>
                    </div>
                    <div>
                        @if($ticket->event->venue_name)
                        <div class="detail-item">
                            <div class="detail-label">Venue</div>
                            <div class="detail-value">{{ $ticket->event->venue_name }}</div>
                        </div>
                        @if($ticket->event->venue_address)
                        <div class="detail-item">
                            <div class="detail-label">Address</div>
                            <div class="detail-value">{{ $ticket->event->venue_address }}</div>
                        </div>
                        @endif
                        @endif
                        @if($ticket->event->organizer)
                        <div class="detail-item">
                            <div class="detail-label">Organizer</div>
                            <div class="detail-value">{{ $ticket->event->organizer->name }}</div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Ticket Status -->
            <div class="section">
                <div class="section-title">Ticket Status</div>
                <div class="details-grid">
                    <div>
                        <div class="detail-item">
                            <div class="detail-label">Order Number</div>
                            <div class="detail-value">{{ $ticket->order->order_number }}</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Current Status</div>
                            <div class="detail-value">
                                <span class="status status-{{ $ticket->status }}">
                                    {{ ucfirst($ticket->status) }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div>
                        @if($ticket->used_at)
                        <div class="detail-item">
                            <div class="detail-label">Used At</div>
                            <div class="detail-value">
                                {{ \Carbon\Carbon::parse($ticket->used_at)->format('M j, Y g:i A') }}
                            </div>
                        </div>
                        @endif
                        <div class="detail-item">
                            <div class="detail-label">Generated</div>
                            <div class="detail-value">
                                {{ \Carbon\Carbon::parse($ticket->created_at)->format('M j, Y g:i A') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>



            <!-- Important Notes -->
            <div class="important-notes">
                <h4>Important Information:</h4>
                <ul>
                    <li>Please arrive at least 30 minutes before the event starts</li>
                    <li>Bring a valid ID that matches the attendee name on this ticket</li>
                    <li>This ticket is non-transferable and non-refundable</li>
                    <li>Photography and recording policies may apply at the venue</li>
                    <li>Keep this ticket safe - it contains your entry information</li>
                </ul>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p style="margin: 0 0 4px 0;">
                <strong>Suptix</strong> - Your trusted event ticketing platform
            </p>
            <p style="margin: 0;">
                Generated on {{ now()->format('F j, Y \a\t g:i A') }}
            </p>
        </div>
    </div>
</body>
</html>
