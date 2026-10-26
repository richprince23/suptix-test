<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DemoRequestMail extends Mailable
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
            subject: 'New Demo Request - '.$this->demoData['company'].' ('.$this->demoData['timeline'].')',
            replyTo: $this->demoData['email']
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.demo-request',
            with: [
                'demoData' => $this->demoData,
            ]
        );
    }
}
