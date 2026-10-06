<?php

use App\Enums\PublishStatus;
use App\Models\Episode;
use App\Presenters\EpisodePresenter;
use Illuminate\Support\Carbon;

it('formats an episode code when its optional episode number is missing', function () {
    $episode = new Episode([
        'season_number' => 2,
        'episode_number' => null,
    ]);

    expect(EpisodePresenter::from($episode)->code())->toBe('S02E00');
});

it('generates SEO data when its podcast is missing', function () {
    $episode = new Episode([
        'title' => 'Orphaned Episode',
        'description' => 'An episode without an available podcast.',
    ]);

    $seo = $episode->getDynamicSEOData();

    expect($seo->title)->toBe('Orphaned Episode — Podcast')
        ->and($seo->description)
        ->toBe('An episode without an available podcast.');
});

it('knows whether it is publicly published', function (?PublishStatus $status, ?Carbon $publishedAt, bool $expected) {
    $episode = new Episode([
        'status' => $status,
        'published_at' => $publishedAt,
    ]);

    expect($episode->isPublished())->toBe($expected);
})->with([
    [PublishStatus::Published, fn (): Carbon => now()->subMinute(), true],
    [PublishStatus::Published, fn (): Carbon => now()->addMinute(), false],
    [PublishStatus::Published, null, false],
    [PublishStatus::Draft, fn (): Carbon => now()->subMinute(), false],
]);

it('turns only a Transistor share URL into its embed URL', function (?string $url, ?string $embedUrl) {
    $episode = new Episode(['transistor_url' => $url]);

    expect($episode->transistorEmbedUrl())
        ->toBe($embedUrl);
})->with([
    'share URL' => ['https://share.transistor.fm/s/428dcd6b', 'https://share.transistor.fm/e/428dcd6b'],
    'share URL with trailing slash' => ['https://share.transistor.fm/s/428dcd6b/', 'https://share.transistor.fm/e/428dcd6b'],
    'missing' => [null, null],
    'insecure' => ['http://share.transistor.fm/s/428dcd6b', null],
    'other host' => ['https://example.com/s/428dcd6b', null],
    'already an embed' => ['https://share.transistor.fm/e/428dcd6b', null],
    'extra path' => ['https://share.transistor.fm/s/428dcd6b/extra', null],
    'query string' => ['https://share.transistor.fm/s/428dcd6b?autoplay=1', null],
]);
