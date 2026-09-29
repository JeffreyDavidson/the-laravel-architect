<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\ContactInquiry;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;

/**
 * Notifies the site owner of a contact inquiry. Sent by SendContactInquiryEmails, which
 * records the send so retries never deliver it twice.
 */
class ContactMessageReceived extends Mailable
{
    public function __construct(public readonly ContactInquiry $inquiry) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            replyTo: [new Address($this->inquiry->email, $this->inquiry->name)],
            subject: "Contact Form: {$this->inquiry->type} - {$this->inquiry->name}",
        );
    }

    public function content(): Content
    {
        return new Content(
            text: 'mail.contact-message-received',
            with: [
                'senderName' => $this->inquiry->name,
                'senderEmail' => $this->inquiry->email,
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
            'Resend-Idempotency-Key' => "tla-contact-{$fingerprint}-notification",
        ]);
    }
}
