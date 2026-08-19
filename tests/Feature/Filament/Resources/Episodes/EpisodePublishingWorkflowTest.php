<?php

use App\Enums\PublishStatus;
use App\Filament\Resources\Episodes\Pages\EditEpisode;
use App\Models\Episode;
use App\Models\Podcast;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->actingAs(User::factory()->create(['is_admin' => true]));
});

function episodeForPublishingWorkflow(PublishStatus $status = PublishStatus::Draft, ?DateTimeInterface $publishedAt = null): Episode
{
    $podcast = Podcast::query()->create([
        'name' => 'Publishing Workflow Podcast',
        'slug' => 'publishing-workflow-podcast',
        'description' => 'Podcast description.',
        'is_active' => true,
    ]);

    return Episode::query()->create([
        'podcast_id' => $podcast->id,
        'title' => 'Episode Publishing Workflow',
        'slug' => 'episode-publishing-workflow',
        'episode_number' => 1,
        'season_number' => 1,
        'description' => 'Episode description.',
        'status' => $status,
        'published_at' => $publishedAt,
    ]);
}

it('publishes an episode through Filament and exposes it publicly', function () {
    $episode = episodeForPublishingWorkflow();

    livewire(EditEpisode::class, ['record' => $episode->getRouteKey()])
        ->fillForm([
            'status' => PublishStatus::Published,
            'published_at' => now()->subMinute(),
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $episode->refresh();

    expect($episode->status)->toBe(PublishStatus::Published);

    $this->get(route('podcast.episode', [$episode->podcast, $episode]))->assertOk();
    $this->get('/sitemap.xml')->assertSee(route('podcast.episode', [$episode->podcast, $episode]), false);
});

it('hides an episode again when Filament changes it back to draft', function () {
    $episode = episodeForPublishingWorkflow(PublishStatus::Published, now()->subMinute());

    livewire(EditEpisode::class, ['record' => $episode->getRouteKey()])
        ->fillForm([
            'status' => PublishStatus::Draft,
            'published_at' => null,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $episode->refresh();

    expect($episode->status)->toBe(PublishStatus::Draft);

    $this->get(route('podcast.episode', [$episode->podcast, $episode]))->assertNotFound();
    $this->get('/sitemap.xml')->assertDontSee(route('podcast.episode', [$episode->podcast, $episode]), false);
});

it('keeps an episode hidden while its published date is scheduled in the future', function () {
    $episode = episodeForPublishingWorkflow();
    $publishedAt = now()->addDay();

    livewire(EditEpisode::class, ['record' => $episode->getRouteKey()])
        ->fillForm([
            'status' => PublishStatus::Published,
            'published_at' => $publishedAt,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $episode->refresh();

    expect($episode->status)->toBe(PublishStatus::Published)
        ->and($episode->published_at->isFuture())->toBeTrue();

    $this->get(route('podcast.episode', [$episode->podcast, $episode]))->assertNotFound();
    $this->get('/sitemap.xml')->assertDontSee(route('podcast.episode', [$episode->podcast, $episode]), false);
});
