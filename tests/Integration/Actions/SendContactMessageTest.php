<?php

use App\Actions\SendContactMessage;
use App\Data\ContactMessageData;
use App\Enums\ContactBudget;
use App\Enums\ContactType;
use App\Mail\ContactMessageConfirmation;
use App\Mail\ContactMessageReceived;
use Illuminate\Support\Facades\Mail;

it('queues the contact message for the site owner and a confirmation for the sender', function () {
    Mail::fake();
    config()->set('mail.contact_to', 'owner@example.com');

    app(SendContactMessage::class)
        ->handle(new ContactMessageData(
            name: 'Jane Doe',
            email: 'jane@example.com',
            type: ContactType::Consulting,
            budget: ContactBudget::Medium,
            message: 'Can you help with an audit?',
        ));

    Mail::assertQueued(
        ContactMessageReceived::class,
        fn (ContactMessageReceived $mail): bool => $mail->hasTo('owner@example.com')
            && $mail->senderName === 'Jane Doe'
            && $mail->senderEmail === 'jane@example.com'
            && $mail->contactType === 'consulting'
            && $mail->budget === 'medium'
            && $mail->contactMessage === 'Can you help with an audit?',
    );
    Mail::assertQueued(
        ContactMessageConfirmation::class,
        fn (ContactMessageConfirmation $mail): bool => $mail->hasTo('jane@example.com', 'Jane Doe')
            && $mail->senderName === 'Jane Doe'
            && $mail->contactType === 'consulting'
            && $mail->budget === 'medium'
            && $mail->contactMessage === 'Can you help with an audit?',
    );
});
