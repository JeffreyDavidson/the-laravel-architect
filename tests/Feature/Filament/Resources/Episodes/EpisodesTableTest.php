<?php

use App\Filament\Resources\Episodes\Pages\ListEpisodes;
use App\Models\Episode;
use App\Models\Podcast;
use App\Models\User;
use App\Presenters\EpisodePresenter;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\PublishableFixtures;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\freezeSecond;
use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => actingAs(User::factory()->create(['is_admin' => true])));

it('links the view on site action to the public episode URL', function () {
    $episode = PublishableFixtures::ready('episode');
    $episode->publish();

    livewire(ListEpisodes::class)
        ->assertActionHasUrl(TestAction::make('view_on_site')->table($episode), route('podcasts.episode', ['podcast' => 'show', 'episode' => 'ready-episode']))
        ->assertActionShouldOpenUrlInNewTab(TestAction::make('view_on_site')->table($episode));
});

it('links the view on site action to a signed preview for a draft episode', function () {
    freezeSecond();
    $episode = PublishableFixtures::ready('episode');
    if (! $episode instanceof Episode) {
        throw new RuntimeException('Expected the fixture to create an Episode.');
    }

    livewire(ListEpisodes::class)
        ->assertActionHasUrl(TestAction::make('view_on_site')->table($episode), EpisodePresenter::from($episode)->previewUrl())
        ->assertActionShouldOpenUrlInNewTab(TestAction::make('view_on_site')->table($episode));
});

it('links the view on site action to a signed preview when the show is inactive', function () {
    freezeSecond();
    $episode = PublishableFixtures::ready('episode', [
        'podcast_id' => Podcast::factory()
            ->inactive()
            ->create()
            ->id,
    ]);
    $episode->publish();
    if (! $episode instanceof Episode) {
        throw new RuntimeException('Expected the fixture to create an Episode.');
    }

    livewire(ListEpisodes::class)
        ->assertActionHasUrl(TestAction::make('view_on_site')->table($episode), EpisodePresenter::from($episode)->previewUrl());
});
