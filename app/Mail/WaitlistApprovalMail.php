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

class WaitlistApprovalMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public RSVP $rsvp,
        public Event $event,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "You're in! Your RSVP for {$this->event->title} is confirmed",
            from: config('mail.from.address'),
            replyTo: $this->event->organizer->email ?? config('mail.from.address'),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.waitlist-approval',
            with: [
                'rsvp' => $this->rsvp,
                'event' => $this->event,
                'guestName' => $this->rsvp->guest_name,
                'eventTitle' => $this->event->title,
                'eventDate' => $this->event->start_date,
                'eventTime' => $this->event->start_time,
                'organizer' => $this->event->organizer,
                'guestCode' => $this->rsvp->guest_code,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
