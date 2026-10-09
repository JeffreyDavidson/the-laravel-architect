<?php

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use App\Queries\RelatedPostsQuery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use JeffreyDavidson\CreatorKit\Enums\PublishStatus;

pest()->use(RefreshDatabase::class);

it('selects related posts by category, shared tags, and latest publication', function () {
    $author = User::factory()->create();
    $category = Category::factory()->create();
    $otherCategory = Category::factory()->create();
    $tag = Tag::factory()->create();
    $post = createRelatedPostsQueryPost($author, $category, 'Current post', now());
    $categoryRelated = createRelatedPostsQueryPost(
        $author,
        $category,
        'Same category',
        now()->subDays(3),
    );
    $tagRelated = createRelatedPostsQueryPost(
        $author,
        $otherCategory,
        'Shared tag',
        now()->subDays(2),
    );
    $tagRelated->attachTag($tag);
    $latest = createRelatedPostsQueryPost(
        $author,
        $otherCategory,
        'Latest fallback',
        now()->subDay(),
    );
    $post->attachTag($tag);

    $relatedPosts = app(RelatedPostsQuery::class)
        ->get($post->load('tags'));

    expect($relatedPosts->modelKeys())->toBe([
        $categoryRelated->getKey(),
        $tagRelated->getKey(),
        $latest->getKey(),
    ]);
});

it('excludes unavailable posts and respects the requested limit', function () {
    $author = User::factory()->create();
    $category = Category::factory()->create();
    $post = createRelatedPostsQueryPost($author, $category, 'Current post', now());
    $newer = createRelatedPostsQueryPost($author, $category, 'Newer post', now()->subDay());
    createRelatedPostsQueryPost($author, $category, 'Older post', now()->subDays(2));
    createRelatedPostsQueryPost(
        $author,
        $category,
        'Draft post',
        null,
        PublishStatus::Draft,
    );
    createRelatedPostsQueryPost(
        $author,
        $category,
        'Scheduled post',
        now()->addDay(),
    );

    $relatedPosts = app(RelatedPostsQuery::class)
        ->get($post->load('tags'), limit: 1);

    expect($relatedPosts->modelKeys())->toBe([$newer->getKey()]);
});

function createRelatedPostsQueryPost(
    User $author,
    Category $category,
    string $title,
    ?DateTimeInterface $publishedAt,
    PublishStatus $status = PublishStatus::Published,
): Post {
    return Post::factory()
        ->for($category)
        ->for($author, 'author')
        ->create([
            'title' => $title,
            'status' => $status,
            'published_at' => $publishedAt,
        ]);
}
