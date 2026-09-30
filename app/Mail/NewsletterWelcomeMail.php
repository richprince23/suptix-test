<?php

namespace App\Mail;

use App\Models\Newsletter;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewsletterWelcomeMail extends Mailable
{
    use Queueable, SerializesModels;

    public $newsletter;

    public function __construct(Newsletter $newsletter)
    {
        $this->newsletter = $newsletter;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Welcome to Suptix Newsletter!',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.newsletter-welcome',
            with: [
                'newsletter' => $this->newsletter,
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
