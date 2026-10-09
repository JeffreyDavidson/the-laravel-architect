<?php

use App\Mail\ContactInquiryReceivedMail;
use App\Models\ContactInquiry;
use Carbon\CarbonImmutable;
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

it('keeps the notification idempotency key stable across releases', function () {
    config()->set('app.url', 'https://thelaravelarchitect.com');
    $inquiry = ContactInquiry::factory()->make([
        'id' => 42,
        'created_at' => CarbonImmutable::parse('2026-01-02 03:04:05', 'UTC'),
    ]);

    $headers = new ContactInquiryReceivedMail($inquiry)
        ->headers()
        ->text;

    expect($headers['Resend-Idempotency-Key'] ?? null)
        ->toBe('tla-contact-e17ecfcb94fd504a0ffcfb303b666f895edbd85ce08eb366b103f64e94f8992c-notification');
});
