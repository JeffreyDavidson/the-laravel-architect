<?php

use App\Enums\PublishStatus;
use App\Filament\Resources\NewsletterIssues\Pages\EditNewsletterIssue;
use App\Jobs\DeliverNewsletterIssue;
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

it('saves unsaved edits before sending a test email', function () {
    Mail::fake();
    $issue = editableNewsletterIssue(['status' => PublishStatus::Draft]);

    livewire(EditNewsletterIssue::class, ['record' => $issue->getRouteKey()])
        ->fillForm([
            'title' => 'Edited title',
            'content' => 'Edited content.',
        ])
        ->callAction('sendTestEmail')
        ->assertHasNoFormErrors();

    Mail::assertSent(NewsletterIssueMail::class, function (NewsletterIssueMail $mail): bool {
        $mail->assertHasSubject('Edited title');
        $mail->assertSeeInHtml('Edited content.');

        return true;
    });
    expect($issue->refresh())
        ->title->toBe('Edited title')
        ->content->toBe('Edited content.');
});

it('saves unsaved edits before sending to subscribers', function () {
    Mail::fake();
    Subscriber::query()->create([
        'email' => 'reader@example.com',
        'subscribed_at' => now(),
        'verified_at' => now(),
    ]);
    $issue = editableNewsletterIssue();

    livewire(EditNewsletterIssue::class, ['record' => $issue->getRouteKey()])
        ->fillForm([
            'title' => 'Edited title',
            'content' => 'Edited content.',
        ])
        ->callAction('sendToSubscribers')
        ->assertHasNoFormErrors()
        ->assertNotified('Queued for 1 subscriber');

    DeliverNewsletterIssue::dispatchSync(NewsletterDelivery::query()->sole());

    Mail::assertSent(NewsletterIssueMail::class, function (NewsletterIssueMail $mail): bool {
        $mail->assertHasSubject('Edited title');
        $mail->assertSeeInHtml('Edited content.');

        return $mail->hasTo('reader@example.com');
    });
});

it('sends nothing when the unsaved edits are invalid', function (string $action) {
    Mail::fake();
    Subscriber::query()->create([
        'email' => 'reader@example.com',
        'subscribed_at' => now(),
        'verified_at' => now(),
    ]);
    $issue = editableNewsletterIssue();

    livewire(EditNewsletterIssue::class, ['record' => $issue->getRouteKey()])
        ->fillForm(['title' => ''])
        ->callAction($action)
        ->assertHasErrors(['data.title' => 'required']);

    Mail::assertNothingSent();
    $issue->refresh();
    expect($issue->title)
        ->toBe('Issue One')
        ->and($issue->wasSent())
        ->toBeFalse();
    $this->assertDatabaseCount('newsletter_deliveries', 0);
})->with(['sendTestEmail', 'sendToSubscribers']);

it('hides the sending actions from a user who is not an administrator', function (string $action) {
    $issue = editableNewsletterIssue();
    $page = livewire(EditNewsletterIssue::class, ['record' => $issue->getRouteKey()]);

    $this->actingAs(User::factory()->create(['is_admin' => false]));

    $page->assertActionHidden($action);
})->with(['sendTestEmail', 'sendToSubscribers']);

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
