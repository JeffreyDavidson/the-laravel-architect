<?php

use App\Models\Post;
use Illuminate\Support\Carbon;
use JeffreyDavidson\CreatorKit\Enums\PublishStatus;

it('returns its cast publishing values', function () {
    $publishedAt = now()->subMinute()
        ->startOfSecond();
    $post = new Post([
        'status' => PublishStatus::Published,
        'published_at' => $publishedAt,
    ]);

    expect($post->publishStatus())->toBe(PublishStatus::Published)
        ->and($post->publishedAt()
            ?->equalTo($publishedAt))
        ->toBeTrue();
});

it('knows whether it is publicly published', function (?PublishStatus $status, ?Carbon $publishedAt, bool $expected) {
    $post = new Post([
        'status' => $status,
        'published_at' => $publishedAt,
    ]);

    expect($post->isPublished())->toBe($expected);
})->with([
    [PublishStatus::Published, fn (): Carbon => now()->subMinute(), true],
    [PublishStatus::Published, fn (): Carbon => now()->addMinute(), false],
    [PublishStatus::Published, null, false],
    [PublishStatus::Draft, fn (): Carbon => now()->subMinute(), false],
]);
