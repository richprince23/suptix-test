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

class RsvpReminderMail extends Mailable implements ShouldQueue
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
            subject: "Reminder: {$this->event->title} is tomorrow",
            from: config('mail.from.address'),
            replyTo: $this->event->organizer->email ?? config('mail.from.address'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.rsvp-reminder',
            with: [
                'rsvp' => $this->rsvp,
                'event' => $this->event,
                'guestName' => $this->rsvp->guest_name,
                'eventTitle' => $this->event->title,
                'eventDate' => $this->event->start_date,
                'eventTime' => $this->event->start_time,
                'venue' => (object) [
                    'name' => $this->event->venue_name,
                    'address' => $this->event->venue_address,
                ],
                'organizer' => $this->event->organizer,
                'guestCode' => $this->rsvp->guest_code,
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
