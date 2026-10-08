<?php

use App\Models\Episode;
use App\Models\Podcast;
use App\ViewModels\EpisodeShowViewModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Date;
use Tests\Support\StructuredDataExpectations as Schema;

pest()->use(RefreshDatabase::class);

it('passes presenters, the player and the YouTube video to the episode page', function () {
    $podcast = Podcast::factory()->create(['color' => '#2A6FDB']);
    $episode = Episode::factory()
        ->for($podcast)
        ->published()
        ->create([
            'season_number' => 2,
            'episode_number' => 7,
            'youtube_url' => 'https://youtu.be/abcDEF12345',
        ]);

    $data = app(EpisodeShowViewModel::class)
        ->data($podcast, $episode);

    expect($data['podcastPresenter']->displayColor())->toBe('#2A6FDB')
        ->and($data['episodePresenter']->code())
        ->toBe('S02E07')
        ->and($data['embedUrl'])
        ->toBe('https://share.transistor.fm/e/428dcd6b')
        ->and($data['youtubeVideoId'])
        ->toBe('abcDEF12345');
});

it('titles the episode page with its show and keeps the SEO fields saved in the admin', function () {
    $podcast = Podcast::factory()->create(['name' => 'Coffee Chat']);
    $episode = Episode::factory()
        ->for($podcast)
        ->published()
        ->create(['title' => 'Pilot', 'description' => '']);
    $episode->seo()
        ->update([
            'title' => 'Saved title',
            'description' => 'Saved description.',
            'image' => 'https://images.test/pilot.png',
            'canonical_url' => 'https://canonical.test/pilot',
        ]);

    $data = app(EpisodeShowViewModel::class)
        ->data($podcast, $episode->refresh());
    $seo = $data['pageMeta']->seo;

    expect($seo->title)->toBe('Pilot — Coffee Chat')
        ->and($seo->description)
        ->toBe('')
        ->and($seo->image)
        ->toBe('https://images.test/pilot.png')
        ->and($seo->canonical_url)
        ->toBe('https://canonical.test/pilot');
});

it('describes an episode within its series with three-level breadcrumbs', function () {
    Schema::useFixedOrigin();
    $podcast = Podcast::factory()->create([
        'name' => 'Coffee Chat',
        'slug' => 'coffee-chat',
        'description' => '',
    ]);
    $episode = Episode::factory()
        ->for($podcast)
        ->published()
        ->create([
            'title' => 'Pilot',
            'slug' => 'pilot',
            'description' => 'The first one.',
            'published_at' => Date::parse('2026-08-28 12:00:00'),
            'episode_number' => 1,
            'duration_seconds' => 3725,
        ]);

    $data = app(EpisodeShowViewModel::class)
        ->data($podcast, $episode);

    expect(Schema::graph($data['pageMeta']))->toBe([
        Schema::website(),
        [
            '@type' => 'PodcastSeries',
            '@id' => 'https://example.test/podcasts/coffee-chat#podcast',
            'name' => 'Coffee Chat',
            'url' => 'https://example.test/podcasts/coffee-chat',
            'author' => Schema::author(),
        ],
        [
            '@type' => 'PodcastEpisode',
            '@id' => 'https://example.test/podcasts/coffee-chat/pilot#episode',
            'name' => 'Pilot',
            'url' => 'https://example.test/podcasts/coffee-chat/pilot',
            'mainEntityOfPage' => 'https://example.test/podcasts/coffee-chat/pilot',
            'partOfSeries' => [
                '@type' => 'PodcastSeries',
                '@id' => 'https://example.test/podcasts/coffee-chat#podcast',
            ],
            'description' => 'The first one.',
            'datePublished' => '2026-08-28T12:00:00+00:00',
            'episodeNumber' => 1,
            'duration' => 'PT1H2M5S',
        ],
        Schema::breadcrumbs([
            ['Home', 'https://example.test'],
            ['Podcast', 'https://example.test/podcasts'],
            ['Coffee Chat', 'https://example.test/podcasts/coffee-chat'],
            ['Pilot', 'https://example.test/podcasts/coffee-chat/pilot'],
        ]),
    ]);
});

it('decides when the description and the coming soon notice stand in for the episode', function (array $attributes, bool $tagged, bool $descriptionFallback, bool $detailsComingSoon) {
    /** @var array<string, mixed> $attributes */
    $podcast = Podcast::factory()->create();
    $episode = Episode::factory()
        ->for($podcast)
        ->published()
        ->create([
            'transistor_url' => null,
            ...$attributes,
        ]);

    if ($tagged) {
        $episode->attachTag('Architecture');
    }

    $data = app(EpisodeShowViewModel::class)
        ->data($podcast, $episode);

    expect($data['showDescriptionFallback'])->toBe($descriptionFallback)
        ->and($data['showDetailsComingSoon'])
        ->toBe($detailsComingSoon);
})->with([
    'nothing to show' => [[], false, true, true],
    'a guest' => [['guest_name' => 'Taylor'], false, true, false],
    'a topic' => [[], true, true, false],
    'a Transistor player' => [['transistor_url' => 'https://share.transistor.fm/s/428dcd6b'], false, false, false],
    'a YouTube video' => [['youtube_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ'], false, false, false],
    'show notes' => [['show_notes' => '<p>Notes</p>'], false, false, false],
    'a transcript' => [['transcript' => 'Transcript'], false, false, false],
]);
