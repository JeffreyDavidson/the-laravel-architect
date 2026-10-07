<?php

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use App\Queries\RssFeedQuery;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

it('returns the twenty newest published posts, newest first', function () {
    $author = User::factory()->create();
    $category = Category::factory()->create();

    foreach (range(1, 21) as $position) {
        Post::factory()
            ->for($category)
            ->for($author, 'author')
            ->published()
            ->create([
                'title' => "Feed post {$position}",
                'published_at' => now()->subMinutes($position),
            ]);
    }

    $titles = app(RssFeedQuery::class)
        ->get()
        ->pluck('title');

    expect($titles)
        ->toHaveCount(20)
        ->first()
        ->toBe('Feed post 1')
        ->and($titles)
        ->not->toContain('Feed post 21');
});
