<?php

use App\Models\NewsletterDelivery;
use App\Models\NewsletterIssue;
use App\Models\Post;
use App\Services\NewsletterEmails;
use Illuminate\Foundation\Testing\RefreshDatabase;
use JeffreyDavidson\CreatorKit\Contracts\NewsletterMails;

covers(NewsletterEmails::class);

pest()->use(RefreshDatabase::class);

it('gives the package TLA\'s confirmation email', function () {
    $mail = app(NewsletterMails::class)->confirmation('https://example.test/confirm');

    expect($mail->confirmationUrl)
        ->toBe('https://example.test/confirm');
});

it('gives the package TLA\'s issue email with its unsubscribe link and delivery', function () {
    $issue = NewsletterIssue::factory()->make();
    $delivery = NewsletterDelivery::factory()->make();

    $mail = app(NewsletterMails::class)->issue($issue, 'https://example.test/unsubscribe', $delivery);

    expect($mail->issue)
        ->toBe($issue)
        ->and($mail->unsubscribeUrl)
        ->toBe('https://example.test/unsubscribe')
        ->and($mail->delivery)
        ->toBe($delivery);
});

it('refuses records that are not TLA\'s newsletter models', function () {
    app(NewsletterMails::class)->issue(Post::factory()->make());
})->throws(LogicException::class);
