<?php

namespace App\Mail;

use App\Models\Event;
use App\Models\RsvpWaitlist;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WaitlistConfirmationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public RsvpWaitlist $entry,
        public Event $event,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "You're on the waitlist – {$this->event->title}",
            from: config('mail.from.address'),
            replyTo: $this->event->organizer->email ?? config('mail.from.address'),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.waitlist-confirmation',
            with: [
                'entry' => $this->entry,
                'event' => $this->event,
                'guestName' => $this->entry->guest_name,
                'eventTitle' => $this->event->title,
                'eventDate' => $this->event->start_date,
                'eventTime' => $this->event->start_time,
                'position' => $this->entry->position,
                'organizer' => $this->event->organizer,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
