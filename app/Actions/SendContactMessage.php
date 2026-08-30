<?php

namespace App\Actions;

use App\Mail\ContactMessageConfirmation;
use App\Mail\ContactMessageReceived;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Support\Facades\Mail;

final class SendContactMessage
{
    public function __invoke(
        string $name,
        string $email,
        string $type,
        ?string $budget,
        string $message,
    ): void {
        Mail::to(config('mail.contact_to', config('mail.from.address')))->queue(new ContactMessageReceived(
            senderName: $name,
            senderEmail: $email,
            contactType: $type,
            budget: $budget,
            contactMessage: $message,
        ));
        Mail::to(new Address($email, $name))->queue(new ContactMessageConfirmation(
            senderName: $name,
            contactType: $type,
            budget: $budget,
            contactMessage: $message,
        ));
    }
}
