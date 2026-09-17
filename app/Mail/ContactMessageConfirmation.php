<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactMessageConfirmation extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $senderName,
        public readonly string $contactType,
        public readonly ?string $budget,
        public readonly string $contactMessage,
        public readonly ?string $projectTitle = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: "Got your message, thanks {$this->senderName}!");
    }

    public function content(): Content
    {
        return new Content(text: 'mail.contact-message-confirmation');
    }
}
