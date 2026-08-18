<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ConfirmNewsletterSubscription extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public readonly string $confirmationUrl) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Confirm your subscription');
    }

    public function content(): Content
    {
        return new Content(
            text: 'mail.newsletter-confirmation',
            with: ['confirmationUrl' => $this->confirmationUrl],
        );
    }
}
