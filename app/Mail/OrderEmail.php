<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $order;

    /**
     * Create a new message instance.
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Order Confirmation - {$this->order->order_number}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            html: 'emails.order',
            with: [
                'order' => $this->order,
                'event' => $this->order->event,
                'venue' => (object) [
                    'name' => $this->order->event->venue_name,
                    'address' => $this->order->event->venue_address,
                ],
                'organizer' => $this->order->event->organizer,
                'tickets' => $this->order->tickets,
                'orderSearchUrl' => url("/order-search?order={$this->order->order_number}"),
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
