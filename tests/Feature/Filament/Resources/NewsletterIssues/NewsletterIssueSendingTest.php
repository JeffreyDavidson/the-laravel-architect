<?php

use App\Enums\PublishStatus;
use App\Filament\Resources\NewsletterIssues\Pages\EditNewsletterIssue;
use App\Mail\NewsletterIssueMail;
use App\Models\NewsletterDelivery;
use App\Models\NewsletterIssue;
use App\Models\Subscriber;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    $this->actingAs(
        User::factory()
            ->create(['is_admin' => true]),
    );
});

/** @param array<string, mixed> $overrides */
function editableNewsletterIssue(array $overrides = []): NewsletterIssue
{
    return NewsletterIssue::query()->create([
        'title' => 'Issue One',
        'slug' => 'issue-one',
        'content' => 'Content.',
        'status' => PublishStatus::Published,
        'published_at' => now()->subDay(),
        ...$overrides,
    ]);
}

it('offers sending only for published issues that have not been sent', function (Closure $createIssue, bool $visible) {
    $issue = $createIssue();
    if (! $issue instanceof NewsletterIssue) {
        throw new RuntimeException('The dataset must create a newsletter issue.');
    }

    $page = livewire(EditNewsletterIssue::class, ['record' => $issue->getRouteKey()]);

    $visible
        ? $page->assertActionVisible('sendToSubscribers')
        : $page->assertActionHidden('sendToSubscribers');
})->with([
    'published' => [fn (): NewsletterIssue => editableNewsletterIssue(), true],
    'draft' => [fn (): NewsletterIssue => editableNewsletterIssue(['status' => PublishStatus::Draft]), false],
    'already sent' => [fn (): NewsletterIssue => editableNewsletterIssue(['sent_at' => now()]), false],
]);

it('queues the issue for active subscribers', function () {
    Subscriber::query()->create([
        'email' => 'reader@example.com',
        'subscribed_at' => now(),
        'verified_at' => now(),
    ]);
    $issue = editableNewsletterIssue();

    livewire(EditNewsletterIssue::class, ['record' => $issue->getRouteKey()])
        ->callAction('sendToSubscribers')
        ->assertNotified('Queued for 1 subscriber');

    $issue->refresh();
    expect($issue->wasSent())
        ->toBeTrue();
    $this->assertDatabaseCount('newsletter_deliveries', 1);
});

it('sends a test email to the site owner', function () {
    Mail::fake();
    config()->set('mail.contact_to', 'owner@example.com');
    $issue = editableNewsletterIssue(['status' => PublishStatus::Draft]);

    livewire(EditNewsletterIssue::class, ['record' => $issue->getRouteKey()])
        ->callAction('sendTestEmail')
        ->assertNotified('Test email sent to owner@example.com');

    Mail::assertSent(NewsletterIssueMail::class, fn (NewsletterIssueMail $mail): bool => $mail->hasTo('owner@example.com')
        && $mail->unsubscribeUrl === null);
    $issue->refresh();
    expect($issue->wasSent())
        ->toBeFalse();
});

it('shows delivery progress after sending', function () {
    $issue = editableNewsletterIssue(['sent_at' => now()]);
    $issueId = $issue->getKey();
    NewsletterDelivery::factory()
        ->sent()
        ->create(['newsletter_issue_id' => $issueId]);
    NewsletterDelivery::factory()
        ->create(['newsletter_issue_id' => $issueId]);

    livewire(EditNewsletterIssue::class, ['record' => $issue->getRouteKey()])
        ->assertSee('Delivered to 1 of 2 subscribers');
});
