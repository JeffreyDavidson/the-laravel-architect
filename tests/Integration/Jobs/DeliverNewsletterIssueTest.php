<?php

use App\Jobs\DeliverNewsletterIssue;
use App\Mail\NewsletterIssueMail;
use App\Models\NewsletterDelivery;
use App\Models\Subscriber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Queue\Middleware\RateLimited;
use Illuminate\Support\Facades\Mail;

pest()->use(RefreshDatabase::class);

function runDelivery(NewsletterDelivery $delivery): void
{
    app()->call([new DeliverNewsletterIssue($delivery), 'handle']);
}

it('emails the issue with a personal unsubscribe link and marks the delivery sent', function () {
    Mail::fake();
    $delivery = NewsletterDelivery::factory()
        ->create();

    runDelivery($delivery);

    $subscriber = $delivery->subscriber;
    $issue = $delivery->newsletterIssue;
    if (! $subscriber instanceof Subscriber) {
        throw new RuntimeException('The delivery factory must create a subscriber.');
    }
    $recipient = $subscriber->email;
    Mail::assertSent(NewsletterIssueMail::class, function (NewsletterIssueMail $mail) use ($recipient, $issue): bool {
        $sentIssue = $mail->issue;

        return $mail->hasTo($recipient)
            && $sentIssue->is($issue)
            && str_contains((string) $mail->unsubscribeUrl, 'signature=');
    });
    $delivery->refresh();
    expect($delivery->sent_at)
        ->not
        ->toBeNull();
});

it('does not email a delivery twice', function () {
    Mail::fake();
    $delivery = NewsletterDelivery::factory()
        ->sent()
        ->create();

    runDelivery($delivery);

    Mail::assertNothingSent();
});

it('drops the delivery when the subscriber is no longer active', function () {
    Mail::fake();
    $delivery = NewsletterDelivery::factory()
        ->create();
    $delivery->subscriber?->update(['unsubscribed_at' => now()]);

    runDelivery($delivery);

    Mail::assertNothingSent();
    $this->assertModelMissing($delivery);
});

it('sends through the newsletter delivery rate limit', function () {
    $job = new DeliverNewsletterIssue(NewsletterDelivery::factory()->create());

    expect($job->middleware())
        ->toEqual([new RateLimited('newsletter-delivery')]);
});
