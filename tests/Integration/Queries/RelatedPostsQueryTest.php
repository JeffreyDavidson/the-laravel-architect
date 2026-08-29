<?php

use App\Enums\PublishStatus;
use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use App\Queries\RelatedPostsQuery;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('selects related posts by category, shared tags, and latest publication', function () {
    $author = User::factory()->create();
    $category = Category::query()->create([
        'name' => 'Architecture',
        'slug' => 'architecture',
    ]);
    $otherCategory = Category::query()->create([
        'name' => 'Laravel',
        'slug' => 'laravel',
    ]);
    $tag = Tag::query()->create([
        'name' => 'Boundaries',
        'slug' => 'boundaries',
    ]);
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
    $category = Category::query()->create([
        'name' => 'Architecture',
        'slug' => 'architecture',
    ]);
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
    return Post::query()->create([
        'title' => $title,
        'slug' => str($title)->slug(),
        'content' => 'Post content.',
        'category_id' => $category->id,
        'user_id' => $author->id,
        'status' => $status,
        'published_at' => $publishedAt,
    ]);
}
