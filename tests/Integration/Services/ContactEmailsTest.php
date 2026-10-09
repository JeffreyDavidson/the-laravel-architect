<?php

use App\Models\ContactInquiry;
use App\Models\Video;
use App\Services\ContactEmails;
use JeffreyDavidson\CreatorKit\Contracts\ContactMails;

covers(ContactEmails::class);

it('gives the package TLA\'s contact emails for the inquiry', function () {
    $inquiry = ContactInquiry::factory()->make();
    $mails = app(ContactMails::class);

    $notification = $mails->notification($inquiry);
    $confirmation = $mails->confirmation($inquiry);

    expect($notification->inquiry)
        ->toBe($inquiry)
        ->and($confirmation->inquiry)
        ->toBe($inquiry);
});

it('refuses records that are not TLA\'s contact inquiries', function (string $method) {
    app(ContactMails::class)->{$method}(new Video);
})
    ->with(['notification', 'confirmation'])
    ->throws(LogicException::class);
