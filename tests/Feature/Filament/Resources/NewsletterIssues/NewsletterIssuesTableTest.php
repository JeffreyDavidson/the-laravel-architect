<?php

use App\Filament\Resources\NewsletterIssues\Pages\ListNewsletterIssues;
use App\Models\NewsletterIssue;
use App\Models\User;
use App\Presenters\NewsletterIssuePresenter;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use JeffreyDavidson\CreatorKit\Enums\PublishStatus;
use Tests\Support\PublishableFixtures;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\freezeSecond;
use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => actingAs(User::factory()->create(['is_admin' => true])));

it('links the view on site action to the public issue URL', function () {
    $issue = PublishableFixtures::ready('newsletter issue', [
        'status' => PublishStatus::Published,
        'published_at' => now()->subDay(),
    ]);

    livewire(ListNewsletterIssues::class)
        ->assertActionHasUrl(TestAction::make('view_on_site')->table($issue), route('newsletter.issue', $issue))
        ->assertActionShouldOpenUrlInNewTab(TestAction::make('view_on_site')->table($issue));
});

it('links the view on site action to a signed preview for a draft', function () {
    freezeSecond();
    $issue = PublishableFixtures::ready('newsletter issue');
    if (! $issue instanceof NewsletterIssue) {
        throw new RuntimeException('Expected the fixture to create a NewsletterIssue.');
    }

    livewire(ListNewsletterIssues::class)
        ->assertActionHasUrl(TestAction::make('view_on_site')->table($issue), NewsletterIssuePresenter::from($issue)->previewUrl())
        ->assertActionShouldOpenUrlInNewTab(TestAction::make('view_on_site')->table($issue));
});
