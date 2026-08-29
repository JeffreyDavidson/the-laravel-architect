<?php

use App\Enums\PublishStatus;
use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use App\ViewModels\BlogCategoryViewModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RalphJSmit\Laravel\SEO\Support\SEOData;

uses(RefreshDatabase::class);

it('builds a paginated category archive payload', function () {
    $author = User::factory()->create();
    $category = Category::query()->create([
        'name' => 'Architecture',
        'slug' => 'architecture',
    ]);

    foreach (range(1, 11) as $index) {
        Post::query()->create([
            'title' => "Architecture Article {$index}",
            'slug' => "architecture-article-{$index}",
            'content' => 'A maintainable application starts with clear boundaries.',
            'category_id' => $category->getKey(),
            'user_id' => $author->getKey(),
            'status' => PublishStatus::Published,
            'published_at' => now()->subDays($index),
        ]);
    }

    Post::query()->create([
        'title' => 'Architecture Draft',
        'slug' => 'architecture-draft',
        'content' => 'This draft must remain private.',
        'category_id' => $category->getKey(),
        'user_id' => $author->getKey(),
        'status' => PublishStatus::Draft,
    ]);

    request()->query->set('page', 2);

    $data = app(BlogCategoryViewModel::class)
        ->data($category);

    $canonicalUrl = route('blog.category', ['category' => $category, 'page' => 2]);

    expect($data)->toHaveKeys(['category', 'posts', 'seoSource'])
        ->and($data['category']->is($category))->toBeTrue()
        ->and($data['posts']->currentPage())->toBe(2)
        ->and($data['posts']->total())->toBe(11)
        ->and($data['posts']->sole()->relationLoaded('tags'))->toBeTrue()
        ->and($data['posts']->sole()->relationLoaded('author'))->toBeTrue()
        ->and($data['seoSource'])->toBeInstanceOf(SEOData::class)
        ->and($data['seoSource']->title)->toBe('Architecture Articles — Page 2')
        ->and($data['seoSource']->description)->toBe(
            'Articles about Architecture — Laravel development insights from Jeffrey Davidson. Page 2 of 2.',
        )
        ->and($data['seoSource']->url)->toBe($canonicalUrl)
        ->and($data['seoSource']->canonical_url)->toBe($canonicalUrl);
});
