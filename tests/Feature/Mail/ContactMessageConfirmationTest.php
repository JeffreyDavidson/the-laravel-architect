<?php

use App\Mail\ContactMessageConfirmation;
use App\Models\ContactInquiry;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

it('renders the inquiry details', function () {
    $inquiry = ContactInquiry::factory()->create([
        'name' => 'Private Sender',
        'email' => 'private@example.test',
        'type' => 'consulting',
        'message' => 'Confidential message',
    ]);

    $mail = new ContactMessageConfirmation($inquiry);

    $envelope = $mail->envelope();

    expect($envelope->subject)
        ->toBe('Got your message, thanks Private Sender!')
        ->and($mail->render())
        ->toContain('Confidential message');
});
