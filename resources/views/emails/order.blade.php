<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation</title>
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
            font-size: 18px;
            opacity: 0.9;
        }
        .order-summary {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            padding: 24px 32px;
            text-align: center;
        }
        .order-summary h2 {
            margin: 0 0 8px 0;
            font-size: 24px;
        }
        .order-summary .order-number {
            font-size: 16px;
            opacity: 0.9;
            margin-bottom: 12px;
        }
        .order-summary .total {
            font-size: 32px;
            font-weight: bold;
            margin: 0;
        }
        .content {
            padding: 32px;
        }
        .section {
            margin-bottom: 32px;
        }
        .section-title {
            font-size: 20px;
            font-weight: bold;
            color: #374151;
            margin: 0 0 16px 0;
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 8px;
        }
        .event-info {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border: 2px solid #e5e7eb;
            border-radius: 0.75rem;
            padding: 24px;
            margin-bottom: 24px;
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
            font-size: 14px;
        }
        .detail-value {
            color: #374151;
            font-size: 16px;
            margin-top: 4px;
        }
        .ticket-list {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 0.75rem;
            overflow: hidden;
        }
        .ticket-item {
            padding: 16px 20px;
            border-bottom: 1px solid #f3f4f6;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .ticket-item:last-child {
            border-bottom: none;
        }
        .ticket-item:nth-child(even) {
            background: #f9fafb;
        }
        .ticket-info {
            flex: 1;
        }
        .ticket-name {
            font-weight: bold;
            color: #374151;
            margin-bottom: 4px;
        }
        .ticket-details {
            font-size: 14px;
            color: #6b7280;
        }
        .ticket-price {
            font-weight: bold;
            color: #10b981;
            font-size: 18px;
        }
        .cta-section {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border: 2px solid #e5e7eb;
            border-radius: 0.75rem;
            padding: 24px;
            text-align: center;
            margin: 24px 0;
        }
        .cta-button {
            display: inline-block;
            background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%);
            color: white;
            text-decoration: none;
            padding: 14px 28px;
            border-radius: 0.75rem;
            font-weight: bold;
            font-size: 16px;
            margin: 12px 8px;
        }
        .cta-button.secondary {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }
        .footer {
            background-color: #f9fafb;
            padding: 24px 32px;
            text-align: center;
            font-size: 14px;
            color: #6b7280;
            border-top: 1px solid #e5e7eb;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 0.75rem;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-confirmed {
            background: #d1fae5;
            color: #065f46;
        }
        .status-pending {
            background: #fef3cd;
            color: #92400e;
        }
        @media (max-width: 600px) {
            .details-grid {
                grid-template-columns: 1fr;
            }
            .container {
                margin: 16px;
                border-radius: 0.75rem;
            }
            .ticket-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 8px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>Order Confirmation</h1>
            <p>Thank you for your purchase!</p>
        </div>

        <!-- Order Summary -->
        <div class="order-summary">
            <h2>{{ $order->status === 'confirmed' ? 'Payment Confirmed' : 'Order Confirmed' }}</h2>
            <div class="order-number">Order #{{ $order->order_number }}</div>
            <div class="total">GH₵{{ number_format($order->total_amount, 2) }}</div>
            <span class="status-badge {{ $order->status === 'confirmed' ? 'status-confirmed' : 'status-pending' }}">
                {{ ucfirst($order->status) }}
            </span>
            @if($order->status === 'confirmed' && $order->purchased_at)
            <div style="font-size: 14px; opacity: 0.9; margin-top: 8px;">
                Payment processed on {{ \Carbon\Carbon::parse($order->purchased_at)->format('F j, Y g:i A') }}
            </div>
            @endif
        </div>

        <!-- Content -->
        <div class="content">
            <!-- Greeting -->
            <div class="section">
                <p style="font-size: 16px; line-height: 1.6; margin-bottom: 16px;">
                    Hello! Your {{ $order->status === 'confirmed' ? 'payment has been successfully processed and your' : '' }} order for <strong>{{ $event->title }}</strong> has been confirmed.
                    @if($order->status === 'confirmed')
                    Your tickets are now active and ready to use.
                    @endif
                    You can find all your order details and tickets below.
                </p>
            </div>

            <!-- Event Information -->
            <div class="section">
                <h3 class="section-title">Event Details</h3>
                <div class="event-info">
                    <h4 style="margin: 0 0 16px 0; color: #374151; font-size: 18px;">{{ $event->title }}</h4>

                    @if($event->description)
                    <p style="margin: 0 0 16px 0; color: #6b7280; line-height: 1.5;">{{ $event->description }}</p>
                    @endif

                    <div class="details-grid">
                        <div>
                            <div class="detail-item">
                                <div class="detail-label">📅 Date & Time</div>
                                <div class="detail-value">
                                    {{ \Carbon\Carbon::parse($event->start_date)->format('l, F j, Y') }}<br>
                                    {{ \Carbon\Carbon::parse($event->start_time)->format('g:i A') }} -
                                    {{ \Carbon\Carbon::parse($event->end_time)->format('g:i A') }}
                                </div>
                            </div>

                            @if($event->mode === 'online' || $event->mode === 'hybrid')
                            <div class="detail-item">
                                <div class="detail-label">🌐 Platform</div>
                                <div class="detail-value">{{ $event->platform_name }}</div>
                            </div>

                            @if($event->online_link)
                            <div class="detail-item">
                                <div class="detail-label">🔗 Meeting Link</div>
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
                                <div class="detail-label">📍 Venue</div>
                                <div class="detail-value">
                                    {{ $venue->name }}
                                    @if($venue->address)<br>{{ $venue->address }}@endif
                                </div>
                            </div>
                            @endif
                        </div>

                        <div>
                            @if($organizer)
                            <div class="detail-item">
                                <div class="detail-label">👤 Organizer</div>
                                <div class="detail-value">{{ $organizer->name }}</div>
                            </div>
                            @endif

                            <div class="detail-item">
                                <div class="detail-label">🎫 Total Tickets</div>
                                <div class="detail-value">{{ $tickets->count() }} ticket(s)</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Information -->
            <div class="section">
                <h3 class="section-title">Order Information</h3>
                <div class="details-grid">
                    <div>
                        <div class="detail-item">
                            <div class="detail-label">Order Number</div>
                            <div class="detail-value">{{ $order->order_number }}</div>
                        </div>

                        <div class="detail-item">
                            <div class="detail-label">Order Date</div>
                            <div class="detail-value">{{ \Carbon\Carbon::parse($order->created_at)->format('F j, Y g:i A') }}</div>
                        </div>
                    </div>

                    <div>
                        <div class="detail-item">
                            <div class="detail-label">Buyer Email</div>
                            <div class="detail-value">{{ $order->buyer_email }}</div>
                        </div>

                        @if($order->buyer_phone)
                        <div class="detail-item">
                            <div class="detail-label">Phone Number</div>
                            <div class="detail-value">{{ $order->buyer_phone }}</div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Tickets -->
            <div class="section">
                <h3 class="section-title">Your Tickets</h3>
                <div class="ticket-list">
                    @foreach($tickets as $index => $ticket)
                    <div class="ticket-item">
                        <div class="ticket-info">
                            <div class="ticket-name">Ticket #{{ $index + 1 }} - {{ $ticket->attendee_name }}</div>
                            <div class="ticket-details">
                                {{ $ticket->attendee_email }} •
                                {{ $ticket->ticketType->name ?? 'Standard' }} •
                                Ticket #{{ $ticket->ticket_number }}
                            </div>
                        </div>
                        <div class="ticket-price">GHS {{ $ticket->price }}</div>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Call to Action -->
            <div class="cta-section">
                <h4 style="margin: 0 0 12px 0; color: #374151;">Access Your Tickets</h4>
                <p style="margin: 0 0 20px 0; color: #6b7280;">
                    View and download your tickets, or share them with attendees
                </p>

                <a href="{{ $orderSearchUrl }}" class="cta-button">
                    View My Tickets
                </a>

                <p style="margin: 16px 0 0 0; color: #6b7280; font-size: 14px;">
                    You can also search for your order using: <strong>{{ $order->order_number }}</strong>
                </p>
            </div>

            <!-- Important Notes -->
            <div style="background: #fef3cd; border: 1px solid #f59e0b; border-radius: 0.75rem; padding: 16px; margin-top: 24px;">
                <h4 style="margin: 0 0 8px 0; color: #92400e;">📋 Important Reminders:</h4>
                <ul style="margin: 0; padding-left: 20px; color: #92400e;">
                    <li>Individual tickets will be sent to each attendee's email address</li>
                    <li>Please arrive at least 30 minutes before the event starts</li>
                    <li>Bring a valid ID that matches the attendee name on the ticket</li>
                    <li>Keep this order confirmation email for your records</li>
                    <li>Tickets are non-transferable and non-refundable unless otherwise stated</li>
                </ul>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p style="margin: 0 0 8px 0;">
                <strong>Suptix</strong> - Your trusted event ticketing platform
            </p>
            <p style="margin: 0;">
                If you have any questions about your order, please contact the event organizer or visit our support center.
            </p>
            <p style="margin: 12px 0 0 0; font-size: 12px;">
                Order Date: {{ \Carbon\Carbon::parse($order->created_at)->format('F j, Y g:i A') }}
            </p>
        </div>
    </div>
</body>
</html>
