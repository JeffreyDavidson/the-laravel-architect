<?php

namespace App\Actions;

use App\Data\ContactMessageData;
use App\Mail\ContactMessageConfirmation;
use App\Mail\ContactMessageReceived;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Support\Facades\Mail;

final class SendContactMessage
{
    public function handle(ContactMessageData $data): void
    {
        Mail::to(config('mail.contact_to', config('mail.from.address')))->queue(new ContactMessageReceived(
            senderName: $data->name,
            senderEmail: $data->email,
            contactType: $data->type->value,
            budget: $data->budget?->value,
            contactMessage: $data->message,
        ));
        Mail::to(new Address($data->email, $data->name))->queue(new ContactMessageConfirmation(
            senderName: $data->name,
            contactType: $data->type->value,
            budget: $data->budget?->value,
            contactMessage: $data->message,
        ));
    }
}
