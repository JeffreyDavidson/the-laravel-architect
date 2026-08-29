<?php

use App\Enums\PublishStatus;
use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use App\ViewModels\BlogIndexViewModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RalphJSmit\Laravel\SEO\Support\SEOData;

uses(RefreshDatabase::class);

it('builds the public blog index payload', function () {
    $author = User::factory()->create();
    $category = Category::query()->create([
        'name' => 'Architecture',
        'slug' => 'architecture',
    ]);
    $olderPost = createBlogIndexViewModelPost(
        author: $author,
        category: $category,
        title: 'Older Post',
        publishedAt: now()->subDays(2),
    );
    $newerPost = createBlogIndexViewModelPost(
        author: $author,
        category: $category,
        title: 'Newer Post',
        publishedAt: now()->subDay(),
    );
    createBlogIndexViewModelPost(
        author: $author,
        category: $category,
        title: 'Draft Post',
        publishedAt: null,
        status: PublishStatus::Draft,
    );

    $data = app(BlogIndexViewModel::class)
        ->data();

    expect($data)->toHaveKeys(['posts', 'categories', 'seoSource'])
        ->and($data['posts']->modelKeys())->toBe([
            $newerPost->getKey(),
            $olderPost->getKey(),
        ])
        ->and($data['posts']->every(
            fn (Post $post): bool => $post->relationLoaded('category')
                && $post->relationLoaded('tags')
                && $post->relationLoaded('author'),
        ))->toBeTrue()
        ->and($data['categories']->sole()->posts_count)->toBe(2)
        ->and($data['seoSource'])->toBeInstanceOf(SEOData::class);
});

function createBlogIndexViewModelPost(
    User $author,
    Category $category,
    string $title,
    ?DateTimeInterface $publishedAt,
    PublishStatus $status = PublishStatus::Published,
): Post {
    return Post::query()->create([
        'title' => $title,
        'slug' => str($title)->slug(),
        'content' => "{$title} content.",
        'user_id' => $author->getKey(),
        'category_id' => $category->getKey(),
        'status' => $status,
        'published_at' => $publishedAt,
    ]);
}
