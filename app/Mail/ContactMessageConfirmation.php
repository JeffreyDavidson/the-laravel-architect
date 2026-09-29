<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\ContactInquiry;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;

/**
 * Confirms a contact inquiry to its sender. Sent by SendContactInquiryEmails, which
 * records the send so retries never deliver it twice.
 */
class ContactMessageConfirmation extends Mailable
{
    public function __construct(public readonly ContactInquiry $inquiry) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: "Got your message, thanks {$this->inquiry->name}!");
    }

    public function content(): Content
    {
        return new Content(
            text: 'mail.contact-message-confirmation',
            with: [
                'senderName' => $this->inquiry->name,
                'contactType' => $this->inquiry->type,
                'budget' => $this->inquiry->budget,
                'contactMessage' => $this->inquiry->message,
                'projectTitle' => $this->inquiry->project_title,
            ],
        );
    }

    public function headers(): Headers
    {
        $fingerprint = hash('sha256', implode('|', [
            config()->string('app.url'),
            $this->inquiry->id,
            $this->inquiry->created_at?->toISOString(),
        ]));

        return new Headers(text: [
            'Resend-Idempotency-Key' => "tla-contact-{$fingerprint}-confirmation",
        ]);
    }
}
