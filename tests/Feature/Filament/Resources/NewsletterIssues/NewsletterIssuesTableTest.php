<?php

use App\Models\NewsletterIssue;
use App\Models\User;
use App\Presenters\NewsletterIssuePresenter;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use JeffreyDavidson\CreatorKit\Enums\PublishStatus;
use JeffreyDavidson\CreatorKit\Filament\Resources\NewsletterIssues\Pages\ListNewsletterIssues;
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

it('lists issues under tabs by publication state', function (string $tab, array $expected) {
    /** @var list<string> $expected */
    freezeSecond();
    $issues = collect([
        'live' => ['published_at' => now()->subDay()],
        'scheduled' => ['published_at' => now()->addDay()],
        'draft' => ['published_at' => null],
    ])->map(function (array $attributes, string $key): NewsletterIssue {
        $issue = PublishableFixtures::ready('newsletter issue', ['slug' => "{$key}-issue", ...$attributes]);

        if ($key !== 'draft') {
            $issue->publish();
        }

        return $issue;
    });

    livewire(ListNewsletterIssues::class, ['activeTab' => $tab])
        ->assertCanSeeTableRecords($issues->only($expected))
        ->assertCanNotSeeTableRecords($issues->except($expected));
})->with([
    'all' => ['all', ['live', 'scheduled', 'draft']],
    'drafts' => ['drafts', ['draft']],
    'scheduled' => ['scheduled', ['scheduled']],
    'published' => ['published', ['live']],
]);
