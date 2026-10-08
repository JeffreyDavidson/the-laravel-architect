<?php

use App\Mail\ContactInquiryReceivedMail;
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

    $mail = new ContactInquiryReceivedMail($inquiry);

    $envelope = $mail->envelope();

    expect($envelope->subject)
        ->toBe('Contact Form: consulting - Private Sender')
        ->and($mail->render())
        ->toContain('Confidential message', 'private@example.test');
});

it('renders the sender\'s input as raw plain text', function () {
    $inquiry = ContactInquiry::factory()->make([
        'name' => 'Tom & Jerry',
        'message' => 'I\'m keen on "Laravel" <3',
        'project_title' => 'Q&A <site>',
    ]);
    $mail = new ContactInquiryReceivedMail($inquiry);

    $body = $mail->render();

    expect($body)
        ->toContain('Name: Tom & Jerry', 'I\'m keen on "Laravel" <3', 'Project: Q&A <site>')
        ->not->toContain('&amp;', '&lt;', '&quot;', '&#039;');
});
