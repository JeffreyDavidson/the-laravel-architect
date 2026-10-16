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

it('leaves draft and scheduled posts out of the feed', function () {
    // Arrange
    Post::factory()
        ->published()
        ->create(['title' => 'Live entry']);
    Post::factory()->create(['title' => 'Draft entry']);
    Post::factory()
        ->scheduled()
        ->create(['title' => 'Scheduled entry']);

    // Act
    $titles = app(RssFeedQuery::class)
        ->get()
        ->pluck('title');

    // Assert
    expect($titles->all())->toBe(['Live entry']);
});
