<?php

use App\Mail\ContactConfirmationMail;
use App\Models\ContactInquiry;
use Carbon\CarbonImmutable;

it('sends a fixed subject and body without echoing the sender\'s input', function (string $name, string $message, string $projectTitle) {
    $inquiry = ContactInquiry::factory()->make([
        'name' => $name,
        'message' => $message,
        'project_title' => $projectTitle,
    ]);
    $mail = new ContactConfirmationMail($inquiry);

    $envelope = $mail->envelope();
    $body = $mail->render();

    expect($envelope->subject)
        ->toBe('Thanks for getting in touch')
        ->and($body)
        ->toContain('I\'ll reply within 24 to 48 hours')
        ->not->toContain($name, $message, $projectTitle, 'spam.example', '<a ');
})->with([
    'a long name carrying a link' => [
        str_pad('Claim your prize at https://spam.example/win ', 255, 'x'),
        'Hello',
        'Website',
    ],
    'a message full of links and HTML' => [
        'Jane',
        str_repeat('<a href="https://spam.example/pills">Cheap pills</a> https://spam.example/offer ', 50),
        'Free money at https://spam.example',
    ],
]);

it('keeps the confirmation idempotency key stable across releases', function () {
    config()->set('app.url', 'https://thelaravelarchitect.com');
    $inquiry = ContactInquiry::factory()->make([
        'id' => 42,
        'created_at' => CarbonImmutable::parse('2026-01-02 03:04:05', 'UTC'),
    ]);

    $headers = new ContactConfirmationMail($inquiry)
        ->headers()
        ->text;

    expect($headers['Resend-Idempotency-Key'] ?? null)
        ->toBe('tla-contact-e17ecfcb94fd504a0ffcfb303b666f895edbd85ce08eb366b103f64e94f8992c-confirmation');
});
