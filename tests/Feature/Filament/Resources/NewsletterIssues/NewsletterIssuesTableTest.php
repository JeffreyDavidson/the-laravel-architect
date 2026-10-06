<?php

use App\Enums\PublishStatus;
use App\Filament\Resources\NewsletterIssues\Pages\ListNewsletterIssues;
use App\Models\User;
use App\Support\Content\PreviewUrlGenerator;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

    livewire(ListNewsletterIssues::class)
        ->assertActionHasUrl(TestAction::make('view_on_site')->table($issue), app(PreviewUrlGenerator::class)->for($issue))
        ->assertActionShouldOpenUrlInNewTab(TestAction::make('view_on_site')->table($issue));
});
