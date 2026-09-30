<?php

namespace App\Mail;

use App\Models\Order;
use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TicketEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $ticket;

    public $order;

    /**
     * Create a new message instance.
     */
    public function __construct(Ticket $ticket, Order $order)
    {
        $this->ticket = $ticket;
        $this->order = $order;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Your Ticket for {$this->order->event->title}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        // QR code data is now just the ticket number
        $ticketJson = $this->ticket->ticket_number;

        return new Content(
            html: 'emails.ticket',
            with: [
                'ticket' => $this->ticket,
                'order' => $this->order,
                'event' => $this->order->event,
                'venue' => (object) [
                    'name' => $this->order->event->venue_name,
                    'address' => $this->order->event->venue_address,
                ],
                'organizer' => $this->order->event->organizer,
                'ticketDetailsUrl' => url("/ticket/{$this->ticket->ticket_number}"),
                'ticketJson' => $ticketJson,
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
