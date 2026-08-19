<?php

use App\Enums\PublishStatus;
use App\Models\Episode;

it('formats an episode code when its optional episode number is missing', function () {
    $episode = new Episode([
        'season_number' => 2,
        'episode_number' => null,
    ]);

    expect($episode->episode_code)->toBe('S02E00');
});

it('generates SEO data when its podcast is missing', function () {
    $episode = new Episode([
        'title' => 'Orphaned Episode',
        'description' => 'An episode without an available podcast.',
    ]);

    $seo = $episode->getDynamicSEOData();

    expect($seo->title)->toBe('Orphaned Episode — Podcast')
        ->and($seo->description)->toBe('An episode without an available podcast.');
});

it('knows whether it is publicly published', function (?PublishStatus $status, mixed $publishedAt, bool $expected) {
    $episode = new Episode([
        'status' => $status,
        'published_at' => $publishedAt,
    ]);

    expect($episode->isPublished())->toBe($expected);
})->with([
    [PublishStatus::Published, now()->subMinute(), true],
    [PublishStatus::Published, now()->addMinute(), false],
    [PublishStatus::Published, null, false],
    [PublishStatus::Draft, now()->subMinute(), false],
]);
