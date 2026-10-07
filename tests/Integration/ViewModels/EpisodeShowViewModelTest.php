<?php

use App\Models\Episode;
use App\Models\Podcast;
use App\ViewModels\EpisodeShowViewModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

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
