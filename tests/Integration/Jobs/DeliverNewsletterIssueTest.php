<?php

use App\Enums\PublishStatus;
use App\Jobs\DeliverNewsletterIssue;
use App\Mail\NewsletterIssueMail;
use App\Models\NewsletterDelivery;
use App\Models\Subscriber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Queue\Middleware\RateLimited;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;

use function Pest\Laravel\assertModelMissing;

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

it('resends a delivery whose sent stamp was lost with the same provider idempotency key', function () {
    Mail::fake();
    $delivery = NewsletterDelivery::factory()
        ->create();
    runDelivery($delivery);
    $delivery->update(['sent_at' => null]);

    runDelivery($delivery);

    $keys = [];
    foreach (Mail::sent(NewsletterIssueMail::class) as $mail) {
        if ($mail instanceof NewsletterIssueMail) {
            $headers = $mail->headers();
            $keys[] = $headers->text['Resend-Idempotency-Key'] ?? null;
        }
    }
    expect($keys)
        ->toHaveCount(2)
        ->and($keys[0])
        ->toBeString()
        ->toBe($keys[1]);
});

it('times out before the database queue would hand the job to another worker', function () {
    $queue = Queue::connection('database');
    $queue->push(new DeliverNewsletterIssue(NewsletterDelivery::factory()->create()));

    $queuedJob = $queue->pop();

    expect($queuedJob?->timeout())
        ->toBeInt()
        ->toBeLessThan(config()->integer('queue.connections.database.retry_after'));
});

it('drops the delivery when the subscriber is no longer active', function () {
    Mail::fake();
    $delivery = NewsletterDelivery::factory()
        ->create();
    $delivery->subscriber?->update(['unsubscribed_at' => now()]);

    runDelivery($delivery);

    Mail::assertNothingSent();
    assertModelMissing($delivery);
});

it('drops the delivery when the issue is no longer published', function (array $attributes) {
    /** @var array<string, mixed> $attributes */
    Mail::fake();
    $delivery = NewsletterDelivery::factory()
        ->create();
    $delivery->newsletterIssue?->update($attributes);

    runDelivery($delivery);

    Mail::assertNothingSent();
    assertModelMissing($delivery);
})->with([
    'unpublished' => [['status' => PublishStatus::Draft]],
    'moved to a future date' => [['published_at' => now()->addDay()]],
]);

it('sends through the newsletter delivery rate limit', function () {
    $job = new DeliverNewsletterIssue(NewsletterDelivery::factory()->create());

    expect($job->middleware())
        ->toEqual([new RateLimited('newsletter-delivery')]);
});
