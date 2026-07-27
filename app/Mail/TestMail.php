<?php

namespace App\Mail;

use App\Support\Branding;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * Sent from the Settings screen to prove the configured mail server works.
 */
class TestMail extends Mailable
{
    public function __construct(public readonly string $requestedBy) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: Branding::name().' — mail configuration test',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.test',
            with: [
                'requestedBy' => $this->requestedBy,
                'company' => Branding::name(),
            ],
        );
    }
}
