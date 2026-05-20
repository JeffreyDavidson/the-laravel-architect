<?php

use App\Enums\PublishStatus;
use App\Models\Episode;

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
