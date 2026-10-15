<?php

use App\Models\Episode;
use App\Models\Podcast;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\get;

pest()->use(RefreshDatabase::class);

require_once __DIR__.'/../../../Support/PublicPageAssertions.php';

use Illuminate\Support\Facades\Date;

use function Pest\Laravel\travelTo;

/**
 * @param  array<string, mixed>  $attributes
 * @return array{Podcast, Episode}
 */
function createPublicEpisode(array $attributes = []): array
{
    $podcast = Podcast::factory()->create();
    $episode = Episode::factory()
        ->for($podcast)
        ->published()
        ->create([
            'title' => 'Episode media coverage',
            'transistor_url' => null,
            ...$attributes,
        ]);

    return [$podcast, $episode];
}

it('hides inactive podcasts from public podcast surfaces on podcasts.episode', function () {
    $activePodcast = Podcast::factory()->create();
    $inactivePodcast = Podcast::factory()->inactive()
        ->create();
    $episode = Episode::factory()->for($inactivePodcast)
        ->published()
        ->create();
    $response = get(route('podcasts.episode', [$inactivePodcast, $episode]));
    $response->assertNotFound();
});

it('renders episode show notes and transcript as safe Markdown', function () {
    $podcast = Podcast::factory()->create();
    $episode = Episode::factory()
        ->for($podcast)
        ->published()
        ->create([
            'show_notes' => <<<'MARKDOWN'
### Topics Covered

- Keep **domain boundaries** explicit.
- Read the [Laravel documentation](https://laravel.com/docs).

<script>alert('unsafe')</script>

[Unsafe link](javascript:alert('unsafe'))
MARKDOWN,
            'transcript' => <<<'MARKDOWN'
### Transcript

Welcome to the **episode**.

<script>alert('unsafe')</script>

[Unsafe link](javascript:alert('unsafe'))
MARKDOWN,
        ]);

    $response = get(route('podcasts.episode', [$podcast, $episode]));

    $response
        ->assertOk()
        ->assertSeeHtml('<h3>Topics Covered</h3>')
        ->assertSeeHtml('<strong>domain boundaries</strong>')
        ->assertSeeHtml('<a href="https://laravel.com/docs">Laravel documentation</a>')
        ->assertSee('Read transcript')
        ->assertSee('data-transcript')
        ->assertSee('data-transcript-search')
        ->assertSee('data-transcript-content')
        ->assertSeeHtml('<h3>Transcript</h3>')
        ->assertSeeHtml('<strong>episode</strong>')
        ->assertDontSeeHtml("<script>alert('unsafe')</script>")
        ->assertDontSeeHtml('<a href="javascript:');
});

it('does not render an empty transcript section', function () {
    $podcast = Podcast::factory()->create();
    $episode = Episode::factory()
        ->for($podcast)
        ->published()
        ->create();

    $response = get(route('podcasts.episode', [$podcast, $episode]));

    $response
        ->assertOk()
        ->assertDontSee('Read transcript');
});

it('shows an episode publish date in the display timezone', function () {
    config(['app.display_timezone' => 'America/New_York']);
    travelTo('2026-10-10 12:00:00');
    $podcast = Podcast::factory()->create();
    $episode = Episode::factory()
        ->for($podcast)
        ->published()
        ->create([
            'published_at' => '2026-10-06 01:00:00',
        ]);

    $response = get(route('podcasts.episode', [$podcast, $episode]));

    $response
        ->assertOk()
        ->assertSee('October 05, 2026')
        ->assertSee('Oct 05, 2026')
        ->assertSeeHtml('datetime="2026-10-05"')
        ->assertDontSeeHtml('datetime="2026-10-06"')
        ->assertSeeHtml('"datePublished":"2026-10-06T01:00:00+00:00"');
});

it('does not find an episode under a podcast it does not belong to', function () {
    $podcast = Podcast::factory()->create();
    $otherPodcast = Podcast::factory()->create();
    $episode = Episode::factory()
        ->for($otherPodcast)
        ->published()
        ->create();

    $response = get(route('podcasts.episode', [$podcast, $episode]));

    $response
        ->assertNotFound();
});

it('renders the Transistor player for a valid share URL', function () {
    [$podcast, $episode] = createPublicEpisode([
        'transistor_url' => 'https://share.transistor.fm/s/428dcd6b',
    ]);

    $response = get(route('podcasts.episode', [$podcast, $episode]));

    $response
        ->assertOk()
        ->assertSeeHtml('src="https://share.transistor.fm/e/428dcd6b"')
        ->assertSeeHtml('title="Episode media coverage podcast player"');
});

it('renders no audio player or Spotify and Apple embed', function () {
    [$podcast, $episode] = createPublicEpisode();

    $response = get(route('podcasts.episode', [$podcast, $episode]));

    $response
        ->assertOk()
        ->assertDontSeeHtml('<audio')
        ->assertDontSeeHtml('data-audio-player')
        ->assertDontSeeHtml('<iframe')
        ->assertDontSeeHtml('open.spotify.com')
        ->assertDontSeeHtml('Podcast platform');
});

it('renders no player when the Transistor URL is not a share URL', function () {
    [$podcast, $episode] = createPublicEpisode([
        'transistor_url' => 'https://example.com/s/428dcd6b',
    ]);

    $response = get(route('podcasts.episode', [$podcast, $episode]));

    $response
        ->assertOk()
        ->assertDontSeeHtml('share.transistor.fm')
        ->assertDontSeeHtml('<iframe');
});

it('keeps the YouTube link when an episode has one', function () {
    [$podcast, $episode] = createPublicEpisode([
        'youtube_url' => 'https://www.youtube.com/watch?v=abcdefghijk',
    ]);

    $response = get(route('podcasts.episode', [$podcast, $episode]));

    $response
        ->assertOk()
        ->assertSee('YouTube');
});

it('renders canonical structured data for podcasts and episodes', function () {
    $podcast = Podcast::factory()->create();
    $episode = Episode::factory()->for($podcast)
        ->published()
        ->create([
            'episode_number' => 12,
            'duration_seconds' => 3725,
        ]);

    $response = get(route('podcasts.episode', [$podcast, $episode]));

    $response
        ->assertOk();

    $content = $response->getContent();

    $structuredData = decodeStructuredData($content);
    $graph = structuredDataGraph($structuredData['@graph'] ?? null);
    $podcastSeries = structuredDataObject(collect($graph)->firstWhere('@type', 'PodcastSeries'));
    $podcastEpisode = structuredDataObject(collect($graph)->firstWhere('@type', 'PodcastEpisode'));

    expect($podcastSeries)
        ->toMatchArray([
            '@id' => route('podcasts.show', $podcast).'#podcast',
            'name' => $podcast->name,
            'url' => route('podcasts.show', $podcast),
            'author' => [
                '@type' => 'Person',
                '@id' => route('about').'#person',
            ],
        ])
        ->and($podcastEpisode)
        ->toMatchArray([
            '@id' => route('podcasts.episode', [$podcast, $episode]).'#episode',
            'name' => $episode->title,
            'url' => route('podcasts.episode', [$podcast, $episode]),
            'episodeNumber' => 12,
            'duration' => 'PT1H2M5S',
            'datePublished' => Date::parse($episode->published_at)->toIso8601String(),
            'partOfSeries' => [
                '@type' => 'PodcastSeries',
                '@id' => route('podcasts.show', $podcast).'#podcast',
            ],
        ])
        ->and($podcastEpisode)
        ->not->toHaveKey('associatedMedia');
});

it('renders accessible podcast episode embeds and external links on podcasts.episode', function () {
    $podcast = Podcast::factory()->create(['color' => '#2563eb']);
    $episode = Episode::factory()->for($podcast)
        ->published()
        ->create(['title' => 'Designing Laravel Applications', 'youtube_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ']);
    $response = get(route('podcasts.episode', [$podcast, $episode]));
    $response->assertOk()
        ->assertSeeHtml('Play Designing Laravel Applications on YouTube')
        ->assertSeeHtml('title="Designing Laravel Applications on YouTube"')
        ->assertSeeHtml('www.youtube-nocookie.com/embed/dQw4w9WgXcQ')
        ->assertSeeHtml('data-youtube-facade')
        ->assertSeeHtml('data-youtube-player')
        ->assertSeeHtml('data-youtube-play')
        ->assertSeeHtml('x-data="siteHeader"')
        ->assertDontSeeHtml('src="https://www.youtube.com/embed/')
        ->assertSeeHtml('rel="noopener noreferrer"')
        ->assertSeeHtml('data-podcast-copy-url=')
        ->assertSeeHtml('style="--podcast-color: #2563eb;"')
        ->assertDontSeeHtml('<style>')
        ->assertDontSeeHtml('onclick=');
});
