<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\ContactInquiry;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;
use JeffreyDavidson\CreatorKit\Support\Mail\IdempotencyFingerprint;

/**
 * Notifies the site owner of a contact inquiry. Sent by SendContactInquiryEmails, which
 * records the send so retries never deliver it twice. The subject and body show the
 * inquiry type's stored value.
 */
final class ContactInquiryReceivedMail extends Mailable
{
    public function __construct(public readonly ContactInquiry $inquiry) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            replyTo: [new Address($this->inquiry->email, $this->inquiry->name)],
            subject: "Contact Form: {$this->inquiry->type->value} - {$this->inquiry->name}",
        );
    }

    public function content(): Content
    {
        return new Content(
            text: 'mail.contact-message-received',
            with: [
                'senderName' => $this->inquiry->name,
                'senderEmail' => $this->inquiry->email,
                'contactType' => $this->inquiry->type->value,
                'budget' => $this->inquiry->budget,
                'contactMessage' => $this->inquiry->message,
                'projectTitle' => $this->inquiry->project_title,
            ],
        );
    }

    public function headers(): Headers
    {
        $fingerprint = IdempotencyFingerprint::for($this->inquiry->id, $this->inquiry->created_at);

        return new Headers(text: [
            'Resend-Idempotency-Key' => "tla-contact-{$fingerprint}-notification",
        ]);
    }
}
