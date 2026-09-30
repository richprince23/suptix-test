<?php

namespace App\Mail;

use App\Models\Event;
use App\Models\RSVP;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RsvpNotificationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $rsvp;

    public $event;

    /**
     * Create a new message instance.
     */
    public function __construct(RSVP $rsvp, Event $event)
    {
        $this->rsvp = $rsvp;
        $this->event = $event;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "New RSVP for {$this->event->title}",
            from: config('mail.from.address'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.rsvp-notification',
            with: [
                'rsvp' => $this->rsvp,
                'event' => $this->event,
                'guestName' => $this->rsvp->guest_name,
                'eventTitle' => $this->event->title,
                'totalRsvps' => RSVP::where('event_id', $this->event->id)->count(),
                'venueCapacity' => $this->event->venue_capacity ?? null,
            ],
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
