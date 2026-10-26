<?php

namespace App\Mail;

use App\Models\Organizer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class RsvpDigestMail extends Mailable
// implements ShouldQueue
{
    use Queueable, SerializesModels;

    public int $totalRsvps;
    public int $eventCount;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public Organizer $organizer,
        public Collection $rsvpsByEvent
    ) {
        $this->totalRsvps = $rsvpsByEvent->flatten(1)->count();
        $this->eventCount = $rsvpsByEvent->count();
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = $this->totalRsvps === 1
            ? 'New RSVP for Your Event - Suptix'
            : "You have {$this->totalRsvps} new RSVPs - Suptix";

        return new Envelope(
            subject: $subject,
            replyTo: 'noreply@suptix.live',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.rsvp-digest',
            with: [
                'organizer' => $this->organizer,
                'rsvpsByEvent' => $this->rsvpsByEvent,
                'totalRsvps' => $this->totalRsvps,
                'eventCount' => $this->eventCount,
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
