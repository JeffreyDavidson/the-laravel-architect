<?php

use App\Enums\PublishStatus;
use App\Models\Episode;
use App\Models\Podcast;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

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

it('renders uploaded audio and gives it precedence over hosted audio', function () {
    Storage::fake('public');
    Storage::disk('public')->put('episodes/audio/uploaded.mp3', 'audio');
    [$podcast, $episode] = createPublicEpisode([
        'audio_path' => 'episodes/audio/uploaded.mp3',
        'audio_url' => 'https://example.com/hosted.mp3',
    ]);

    $response = $this->get(route('podcast.episode', [$podcast, $episode]));

    $response->assertOk()->assertSeeHtml(Storage::disk('public')->url('episodes/audio/uploaded.mp3'))->assertDontSeeHtml('https://example.com/hosted.mp3');
});

it('renders hosted audio when no upload exists', function () {
    [$podcast, $episode] = createPublicEpisode([
        'audio_url' => 'https://cdn.example.com/hosted.mp3',
    ]);

    $this->get(route('podcast.episode', [$podcast, $episode]))->assertOk()->assertSeeHtml('https://cdn.example.com/hosted.mp3');
});

it('renders only supported podcast embed URLs in an iframe', function () {
    [$podcast, $episode] = createPublicEpisode([
        'embed_url' => 'https://open.spotify.com/embed/episode/abc123',
    ]);

    $this->get(route('podcast.episode', [$podcast, $episode]))->assertOk()->assertSeeHtml('src="https://open.spotify.com/embed/episode/abc123"')->assertSeeHtml('title="Episode media coverage podcast player"');
});

it('does not render unsupported embed URLs', function () {
    [$podcast, $episode] = createPublicEpisode([
        'embed_url' => 'https://malicious.example/embed/episode/abc123',
    ]);

    $this->get(route('podcast.episode', [$podcast, $episode]))->assertOk()->assertDontSeeHtml('malicious.example')->assertDontSeeHtml('<iframe');
});
