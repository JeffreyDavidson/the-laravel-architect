<?php

use App\Actions\SendContactMessage;
use App\Data\ContactMessageData;
use App\Enums\ContactBudget;
use App\Enums\ContactInquiryStatus;
use App\Enums\ContactType;
use App\Mail\ContactMessageConfirmation;
use App\Mail\ContactMessageReceived;
use App\Models\ContactInquiry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

pest()->use(RefreshDatabase::class);

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
            projectTitle: 'The Laravel Architect',
        ));

    expect(ContactInquiry::query()->sole())
        ->name->toBe('Jane Doe')
        ->email->toBe('jane@example.com')
        ->message->toBe('Can you help with an audit?')
        ->status->toBe(ContactInquiryStatus::New);

    Mail::assertQueued(
        ContactMessageReceived::class,
        fn (ContactMessageReceived $mail): bool => $mail->hasTo('owner@example.com')
            && $mail->senderName === 'Jane Doe'
            && $mail->senderEmail === 'jane@example.com'
            && $mail->contactType === 'consulting'
            && $mail->budget === 'medium'
            && $mail->projectTitle === 'The Laravel Architect'
            && $mail->contactMessage === 'Can you help with an audit?',
    );
    Mail::assertQueued(
        ContactMessageConfirmation::class,
        fn (ContactMessageConfirmation $mail): bool => $mail->hasTo('jane@example.com', 'Jane Doe')
            && $mail->senderName === 'Jane Doe'
            && $mail->contactType === 'consulting'
            && $mail->budget === 'medium'
            && $mail->projectTitle === 'The Laravel Architect'
            && $mail->contactMessage === 'Can you help with an audit?',
    );
});
