<?php

declare(strict_types=1);

namespace App\Mail;

use App\Mail\Concerns\FingerprintsIdempotencyKeys;
use App\Models\ContactInquiry;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;

/**
 * Confirms a contact inquiry to its sender. Sent by SendContactInquiryEmails, which
 * records the send so retries never deliver it twice. The recipient address is
 * visitor-supplied, so the subject and body are fixed and never echo the inquiry;
 * otherwise anyone could use the form to send their own text to any address.
 */
final class ContactConfirmationMail extends Mailable
{
    use FingerprintsIdempotencyKeys;

    public function __construct(public readonly ContactInquiry $inquiry) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Thanks for getting in touch');
    }

    public function content(): Content
    {
        return new Content(text: 'mail.contact-message-confirmation');
    }

    public function headers(): Headers
    {
        return new Headers(text: [
            'Resend-Idempotency-Key' => "tla-contact-{$this->idempotencyFingerprint($this->inquiry->id, $this->inquiry->created_at)}-confirmation",
        ]);
    }
}
