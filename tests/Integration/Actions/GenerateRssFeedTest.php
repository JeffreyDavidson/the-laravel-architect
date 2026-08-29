<?php

use App\Actions\GenerateRssFeed;
use App\Enums\PublishStatus;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('generates a newest-first feed bounded to twenty published posts', function () {
    $author = User::factory()->create();

    foreach (range(1, 21) as $position) {
        Post::query()->create([
            'title' => "Feed post {$position}",
            'slug' => "feed-post-{$position}",
            'content' => "Feed content {$position}.",
            'user_id' => $author->getKey(),
            'status' => PublishStatus::Published,
            'published_at' => now()->subMinutes($position),
        ]);
    }

    $xml = app(GenerateRssFeed::class)();

    expect($xml)
        ->toContain('<title>Feed post 1</title>')
        ->not->toContain('<title>Feed post 21</title>')
        ->and(substr_count($xml, '<item>'))->toBe(20);
});
