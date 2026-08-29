<?php

use App\Enums\PublishStatus;
use App\Models\Episode;
use App\Models\Podcast;
use App\Queries\EpisodeNavigationQuery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

it('finds the closest published episodes before and after the current episode', function () {
    $this->travelTo(Carbon::parse('2026-08-28 12:00:00'));

    $podcast = Podcast::query()->create([
        'name' => 'Architecture Sessions',
        'slug' => 'architecture-sessions',
        'description' => 'Conversations about Laravel architecture.',
        'is_active' => true,
    ]);
    $otherPodcast = Podcast::query()->create([
        'name' => 'Other Sessions',
        'slug' => 'other-sessions',
        'description' => 'A different podcast.',
        'is_active' => true,
    ]);
    $currentEpisode = createEpisodeNavigationEpisode($podcast, 'Current episode', now()->subDays(3));
    createEpisodeNavigationEpisode($podcast, 'Farther previous episode', now()->subDays(5));
    $previousEpisode = createEpisodeNavigationEpisode($podcast, 'Previous episode', now()->subDays(4));
    $nextEpisode = createEpisodeNavigationEpisode($podcast, 'Next episode', now()->subDays(2));
    createEpisodeNavigationEpisode($podcast, 'Farther next episode', now()->subDay());
    createEpisodeNavigationEpisode($podcast, 'Draft episode', now()->subDays(2), PublishStatus::Draft);
    createEpisodeNavigationEpisode($podcast, 'Future episode', now()->addDay());
    createEpisodeNavigationEpisode($otherPodcast, 'Other podcast episode', now()->subDays(2));

    $navigation = app(EpisodeNavigationQuery::class)
        ->get($podcast, $currentEpisode);

    expect($navigation['previous']?->is($previousEpisode))->toBeTrue()
        ->and($navigation['next']?->is($nextEpisode))->toBeTrue();
});

it('returns null at both ends when there are no adjacent published episodes', function () {
    $podcast = Podcast::query()->create([
        'name' => 'Architecture Sessions',
        'slug' => 'architecture-sessions',
        'description' => 'Conversations about Laravel architecture.',
        'is_active' => true,
    ]);
    $episode = createEpisodeNavigationEpisode($podcast, 'Only episode', now()->subDay());

    $navigation = app(EpisodeNavigationQuery::class)
        ->get($podcast, $episode);

    expect($navigation)
        ->toBe([
            'previous' => null,
            'next' => null,
        ]);
});

function createEpisodeNavigationEpisode(
    Podcast $podcast,
    string $title,
    Carbon $publishedAt,
    PublishStatus $status = PublishStatus::Published,
): Episode {
    return Episode::query()->create([
        'podcast_id' => $podcast->id,
        'title' => $title,
        'slug' => Str::slug($title),
        'description' => "Description for {$title}.",
        'status' => $status,
        'published_at' => $publishedAt,
    ]);
}
