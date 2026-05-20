<?php

use App\Enums\PublishStatus;
use App\Models\Post;

it('calculates reading time from post content', function () {
    $post = new Post([
        'content' => str_repeat('word ', 251),
    ]);

    expect($post->reading_time)->toBe(2);
});

it('knows whether it is publicly published', function (?PublishStatus $status, mixed $publishedAt, bool $expected) {
    $post = new Post([
        'status' => $status,
        'published_at' => $publishedAt,
    ]);

    expect($post->isPublished())->toBe($expected);
})->with([
    [PublishStatus::Published, now()->subMinute(), true],
    [PublishStatus::Published, now()->addMinute(), false],
    [PublishStatus::Published, null, false],
    [PublishStatus::Draft, now()->subMinute(), false],
]);
