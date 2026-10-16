<?php

use App\Mail\NewsletterIssueMail;
use App\Models\NewsletterDelivery;
use App\Models\NewsletterIssue;
use App\Models\Subscriber;
use App\Models\User;
use Filament\Http\Middleware\Authenticate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use JeffreyDavidson\CreatorKit\Enums\PublishStatus;
use JeffreyDavidson\CreatorKit\Filament\Resources\NewsletterIssues\Pages\EditNewsletterIssue;
use JeffreyDavidson\CreatorKit\Jobs\DeliverNewsletterIssue;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\withoutMiddleware;
use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    actingAs(
        User::factory()
            ->create(['is_admin' => true]),
    );
});

/** @param array<string, mixed> $overrides */
function editableNewsletterIssue(array $overrides = []): NewsletterIssue
{
    return NewsletterIssue::factory()
        ->published()
        ->create($overrides);
}

it('offers sending for a published issue that has not been sent', function () {
    // Arrange
    $issue = editableNewsletterIssue();

    // Act
    $page = livewire(EditNewsletterIssue::class, ['record' => $issue->getRouteKey()]);

    // Assert
    $page->assertActionVisible('sendToSubscribers');
});

it('does not offer sending for a draft or already sent issue', function (Closure $createIssue) {
    // Arrange
    $issue = $createIssue();
    if (! $issue instanceof NewsletterIssue) {
        throw new RuntimeException('The dataset must create a newsletter issue.');
    }

    // Act
    $page = livewire(EditNewsletterIssue::class, ['record' => $issue->getRouteKey()]);

    // Assert
    $page->assertActionHidden('sendToSubscribers');
})->with([
    'draft' => [fn (): NewsletterIssue => editableNewsletterIssue(['status' => PublishStatus::Draft])],
    'already sent' => [fn (): NewsletterIssue => editableNewsletterIssue(['sent_at' => now()])],
]);

it('queues the issue for active subscribers', function () {
    Subscriber::factory()->create();
    $issue = editableNewsletterIssue();

    livewire(EditNewsletterIssue::class, ['record' => $issue->getRouteKey()])
        ->callAction('sendToSubscribers')
        ->assertNotified('Queued for 1 subscriber');

    $issue->refresh();
    expect($issue->wasSent())
        ->toBeTrue();
    assertDatabaseCount('newsletter_deliveries', 1);
});

it('tells the admin nothing was sent when there are no active subscribers', function () {
    $issue = editableNewsletterIssue();

    livewire(EditNewsletterIssue::class, ['record' => $issue->getRouteKey()])
        ->callAction('sendToSubscribers')
        ->assertNotified('No active subscribers to send to')
        ->assertActionVisible('sendToSubscribers');

    $issue->refresh();
    expect($issue->wasSent())
        ->toBeFalse();
});

it('sends a test email to the site owner', function () {
    Mail::fake();
    config()->set('creator-kit.newsletter.test_recipients', ['owner@example.com']);
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
    Subscriber::factory()->create(['email' => 'reader@example.com']);
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
    Subscriber::factory()->create();
    $issue = editableNewsletterIssue(['title' => 'Issue One']);

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
    assertDatabaseCount('newsletter_deliveries', 0);
})->with(['sendTestEmail', 'sendToSubscribers']);

it('hides the sending actions from a user who is not an administrator', function (string $action) {
    $issue = editableNewsletterIssue();
    $page = livewire(EditNewsletterIssue::class, ['record' => $issue->getRouteKey()]);

    actingAs(User::factory()->create(['is_admin' => false]));

    $page->assertActionHidden($action);
})->with(['sendTestEmail', 'sendToSubscribers']);

it('refuses the sending actions when a non-administrator calls them directly', function (string $action) {
    // Arrange
    // The page is opened by an admin, then the signed-in user loses admin rights.
    Mail::fake();
    Subscriber::factory()->create();
    $issue = editableNewsletterIssue(['title' => 'Issue One']);
    $page = livewire(EditNewsletterIssue::class, ['record' => $issue->getRouteKey()])
        ->assertActionVisible($action);
    // The panel middleware already turns non-administrators away; skip it so the edit page's own authorization is what refuses the call.
    withoutMiddleware(Authenticate::class)
        ->actingAs(User::factory()->create(['is_admin' => false]));

    // Act
    $page->mountAction($action);

    // Assert
    $page->assertForbidden();
    Mail::assertNothingQueued();
    Mail::assertNothingSent();
    assertDatabaseCount('newsletter_deliveries', 0);
    $issue->refresh();
    expect($issue->wasSent())->toBeFalse();
})->with(['sendTestEmail', 'sendToSubscribers']);

it('tells the admin how many active subscribers the send will reach', function () {
    Subscriber::factory()
        ->count(2)
        ->create();
    Subscriber::factory()
        ->pending()
        ->create();
    $issue = editableNewsletterIssue();

    livewire(EditNewsletterIssue::class, ['record' => $issue->getRouteKey()])
        ->mountAction('sendToSubscribers')
        ->assertMountedActionModalSee('This emails the issue to 2 active subscribers. It cannot be undone.');
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
