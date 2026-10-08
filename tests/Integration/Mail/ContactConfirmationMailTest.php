<?php

use App\Mail\ContactConfirmationMail;
use App\Models\ContactInquiry;

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
