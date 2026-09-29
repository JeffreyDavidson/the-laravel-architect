<?php

use App\Enums\PublishStatus;
use App\Models\Episode;
use App\Models\Podcast;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\get;

pest()->use(RefreshDatabase::class);

/**
 * @param  array<string, mixed>  $attributes
 * @return array{Podcast, Episode}
 */
function createPublicEpisode(array $attributes = []): array
{
    $podcast = Podcast::query()->create([
        'name' => 'Architecture Sessions',
        'slug' => 'architecture-sessions',
        'description' => 'Conversations about Laravel architecture.',
        'is_active' => true,
    ]);
    $episode = Episode::query()->create([
        'podcast_id' => $podcast->id,
        'title' => 'Episode media coverage',
        'slug' => 'episode-media-coverage',
        'description' => 'An episode with media.',
        'status' => PublishStatus::Published,
        'published_at' => now()->subDay(),
        ...$attributes,
    ]);

    return [$podcast, $episode];
}

it('renders the Transistor player for a valid share URL', function () {
    [$podcast, $episode] = createPublicEpisode([
        'transistor_url' => 'https://share.transistor.fm/s/428dcd6b',
    ]);

    get(route('podcast.episode', [$podcast, $episode]))
        ->assertOk()
        ->assertSeeHtml('src="https://share.transistor.fm/e/428dcd6b"')
        ->assertSeeHtml('title="Episode media coverage podcast player"');
});

it('renders no audio player or Spotify and Apple embed even when an episode still stores them', function (array $legacy) {
    /** @var array<string, mixed> $legacy */
    [$podcast, $episode] = createPublicEpisode($legacy);

    get(route('podcast.episode', [$podcast, $episode]))
        ->assertOk()
        ->assertDontSeeHtml('<audio')
        ->assertDontSeeHtml('data-audio-player')
        ->assertDontSeeHtml('<iframe')
        ->assertDontSeeHtml('open.spotify.com')
        ->assertDontSeeHtml('Podcast platform');
})->with([
    'uploaded audio' => [['audio_path' => 'episodes/audio/uploaded.mp3']],
    'hosted audio' => [['audio_url' => 'https://cdn.example.com/hosted.mp3']],
    'Spotify embed' => [['embed_url' => 'https://open.spotify.com/embed/episode/abc123']],
]);

it('renders no player when the Transistor URL is not a share URL', function () {
    [$podcast, $episode] = createPublicEpisode([
        'transistor_url' => 'https://example.com/s/428dcd6b',
    ]);

    get(route('podcast.episode', [$podcast, $episode]))
        ->assertOk()
        ->assertDontSeeHtml('share.transistor.fm')
        ->assertDontSeeHtml('<iframe');
});

it('keeps the YouTube link when an episode has one', function () {
    [$podcast, $episode] = createPublicEpisode([
        'youtube_url' => 'https://www.youtube.com/watch?v=abcdefghijk',
    ]);

    get(route('podcast.episode', [$podcast, $episode]))
        ->assertOk()
        ->assertSee('YouTube');
});
