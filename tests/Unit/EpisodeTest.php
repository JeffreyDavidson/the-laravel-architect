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
