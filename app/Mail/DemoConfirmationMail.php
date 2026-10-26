<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DemoConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $demoData;

    public function __construct(array $demoData)
    {
        $this->demoData = $demoData;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Demo Request Received - Next Steps with Suptix',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.demo-confirmation',
            with: [
                'demoData' => $this->demoData,
            ]
        );
    }
}
