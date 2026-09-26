<?php

use App\Actions\SendNewsletterIssue;
use App\Enums\PublishStatus;
use App\Models\NewsletterIssue;
use App\Models\Subscriber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

pest()->use(RefreshDatabase::class);

/** @param array<string, mixed> $overrides */
function newsletterIssueToSend(array $overrides = []): NewsletterIssue
{
    return NewsletterIssue::query()->create([
        'title' => 'Issue One',
        'content' => 'Content.',
        'status' => PublishStatus::Published,
        'published_at' => now()->subDay(),
        ...$overrides,
    ]);
}

function subscriberWithState(string $email, bool $confirmed, bool $unsubscribed = false): Subscriber
{
    return Subscriber::query()->create([
        'email' => $email,
        'subscribed_at' => now()->subMonth(),
        'verified_at' => $confirmed ? now()->subMonth() : null,
        'unsubscribed_at' => $unsubscribed ? now()->subWeek() : null,
    ]);
}

it('queues one delivery for each active subscriber and marks the issue sent', function () {
    $active = subscriberWithState('active@example.com', confirmed: true);
    subscriberWithState('unconfirmed@example.com', confirmed: false);
    subscriberWithState('unsubscribed@example.com', confirmed: true, unsubscribed: true);
    $issue = newsletterIssueToSend();

    $queued = app(SendNewsletterIssue::class)
        ->handle($issue);

    $issue->refresh();
    $wasSent = $issue->wasSent();
    expect($queued)
        ->toBe(1)
        ->and($wasSent)
        ->toBeTrue();
    $this->assertDatabaseHas('newsletter_deliveries', [
        'newsletter_issue_id' => $issue->getKey(),
        'subscriber_id' => $active->getKey(),
        'sent_at' => null,
    ]);
    $this->assertDatabaseCount('newsletter_deliveries', 1);
    $this->assertDatabaseCount('jobs', 1);
});

it('refuses issues that cannot be sent', function (Closure $createIssue, string $message) {
    subscriberWithState('active@example.com', confirmed: true);
    $issue = $createIssue();
    if (! $issue instanceof NewsletterIssue) {
        throw new RuntimeException('The dataset must create a newsletter issue.');
    }

    expect(fn () => app(SendNewsletterIssue::class)->handle($issue))
        ->toThrow(LogicException::class, $message);

    $this->assertDatabaseCount('newsletter_deliveries', 0);
    $this->assertDatabaseCount('jobs', 0);
})->with([
    'draft' => [
        fn (): NewsletterIssue => newsletterIssueToSend(['status' => PublishStatus::Draft]),
        'Only published newsletter issues can be sent.',
    ],
    'scheduled for later' => [
        fn (): NewsletterIssue => newsletterIssueToSend(['published_at' => now()->addDay()]),
        'Only published newsletter issues can be sent.',
    ],
    'already sent' => [
        fn (): NewsletterIssue => newsletterIssueToSend(['sent_at' => now()->subHour()]),
        'This newsletter issue has already been sent.',
    ],
]);

it('rolls back every delivery when a job cannot be queued and allows a clean retry', function () {
    subscriberWithState('first@example.com', confirmed: true);
    subscriberWithState('second@example.com', confirmed: true);
    $issue = newsletterIssueToSend();
    $inserts = 0;
    DB::connection()->beforeExecuting(function (string $query) use (&$inserts): void {
        if (str_starts_with($query, 'insert into "jobs"') && ++$inserts === 2) {
            throw new RuntimeException('Synthetic queue failure.');
        }
    });

    expect(fn () => app(SendNewsletterIssue::class)->handle($issue))
        ->toThrow(RuntimeException::class, 'Synthetic queue failure.');

    $issue->refresh();
    expect($issue->wasSent())
        ->toBeFalse();
    $this->assertDatabaseCount('newsletter_deliveries', 0);
    $this->assertDatabaseCount('jobs', 0);

    app(SendNewsletterIssue::class)
        ->handle($issue);

    $this->assertDatabaseCount('newsletter_deliveries', 2);
    $this->assertDatabaseCount('jobs', 2);
});

it('rejects a separate queue database before creating deliveries', function () {
    config()->set([
        'queue.connections.database.connection' => 'separate',
        'database.connections.separate' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => ''],
    ]);
    subscriberWithState('active@example.com', confirmed: true);
    $issue = newsletterIssueToSend();

    expect(fn () => app(SendNewsletterIssue::class)->handle($issue))
        ->toThrow(LogicException::class, 'Newsletter deliveries must share the application database.');

    $this->assertDatabaseCount('newsletter_deliveries', 0);
});
