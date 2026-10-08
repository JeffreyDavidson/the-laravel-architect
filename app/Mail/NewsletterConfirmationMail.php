<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

final class NewsletterConfirmationMail extends Mailable implements ShouldBeEncrypted, ShouldQueue
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
            html: 'mail.newsletter-confirmation',
            text: 'mail.newsletter-confirmation-text',
            with: ['confirmationUrl' => $this->confirmationUrl],
        );
    }
}
