<?php

use App\Enums\PublishStatus;
use App\Models\Episode;
use App\Models\Podcast;
use App\Queries\EpisodeNavigationQuery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Date;

use function Pest\Laravel\travelTo;

pest()->use(RefreshDatabase::class);

it('uses record IDs to navigate episodes with the same publication timestamp', function () {
    $podcast = Podcast::factory()->create();
    $date = now()->subDay();
    $previous = createEpisodeNavigationEpisode($podcast, 'First', $date);
    $current = createEpisodeNavigationEpisode($podcast, 'Second', $date);
    $next = createEpisodeNavigationEpisode($podcast, 'Third', $date);

    $navigation = app(EpisodeNavigationQuery::class)->get($podcast, $current);

    expect($navigation['previous']?->id)->toBe($previous->id)
        ->and($navigation['next']?->id)
        ->toBe($next->id);
});

it('finds the closest published episodes before and after the current episode', function () {
    travelTo(Date::parse('2026-08-28 12:00:00'));

    $podcast = Podcast::factory()->create();
    $otherPodcast = Podcast::factory()->create();
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
        ->and($navigation['next']?->is($nextEpisode))
        ->toBeTrue();
});

it('returns null at both ends when there are no adjacent published episodes', function () {
    $podcast = Podcast::factory()->create();
    $episode = createEpisodeNavigationEpisode($podcast, 'Only episode', now()->subDay());

    $navigation = app(EpisodeNavigationQuery::class)
        ->get($podcast, $episode);

    expect($navigation)
        ->toBe([
            'previous' => null,
            'next' => null,
        ]);
});

it('returns null navigation for an episode without a publication date', function () {
    $podcast = Podcast::factory()->create();
    $episode = createEpisodeNavigationEpisode($podcast, 'Draft episode', now()->subDay(), PublishStatus::Draft);
    $episode->update(['published_at' => null]);

    $navigation = app(EpisodeNavigationQuery::class)
        ->get($podcast, $episode->refresh());

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
    return Episode::factory()
        ->for($podcast)
        ->create([
            'title' => $title,
            'status' => $status,
            'published_at' => $publishedAt,
        ]);
}
