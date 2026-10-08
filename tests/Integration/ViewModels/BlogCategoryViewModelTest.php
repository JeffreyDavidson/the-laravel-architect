<?php

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use App\ViewModels\BlogCategoryViewModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\StructuredDataExpectations as Schema;

pest()->use(RefreshDatabase::class);

it('builds a paginated category archive payload', function () {
    $author = User::factory()->create();
    $category = Category::factory()->create(['name' => 'Architecture']);

    foreach (range(1, 11) as $index) {
        Post::factory()
            ->for($category)
            ->for($author, 'author')
            ->published()
            ->create(['published_at' => now()->subDays($index)]);
    }

    Post::factory()
        ->for($category)
        ->for($author, 'author')
        ->create();

    request()->query->set('page', 2);

    $data = app(BlogCategoryViewModel::class)
        ->data($category);

    $canonicalUrl = route('blog.category', ['category' => $category, 'page' => 2]);

    expect($data)->toHaveKeys(['category', 'posts', 'pageMeta'])
        ->and($data['category']->is($category))
        ->toBeTrue()
        ->and($data['posts']->currentPage())
        ->toBe(2)
        ->and($data['posts']->total())
        ->toBe(11)
        ->and($data['posts']->sole()
            ->relationLoaded('tags'))
        ->toBeTrue()
        ->and($data['posts']->sole()
            ->relationLoaded('author'))
        ->toBeTrue()
        ->and($data['pageMeta']->seo->title)
        ->toBe('Architecture Articles — Page 2')
        ->and($data['pageMeta']->seo->description)
        ->toBe(
            'Articles about Architecture — Laravel development insights from Jeffrey Davidson. Page 2 of 2.',
        )
        ->and($data['pageMeta']->seo->url)
        ->toBe($canonicalUrl)
        ->and($data['pageMeta']->seo->canonical_url)
        ->toBe($canonicalUrl);
});

it('offsets a paginated category listing', function () {
    Schema::useFixedOrigin();
    $category = Category::factory()->create(['name' => 'Testing', 'slug' => 'testing']);

    foreach (range(1, 11) as $day) {
        Post::factory()
            ->for($category)
            ->published()
            ->create(['title' => "Post {$day}", 'slug' => "post-{$day}", 'published_at' => now()->subDays($day)]);
    }

    request()->query->set('page', 2);

    $data = app(BlogCategoryViewModel::class)
        ->data($category);

    expect(Schema::graph($data['pageMeta']))->toBe([
        Schema::website(),
        ...Schema::collection('Testing Articles', 'https://example.test/blog/category/testing?page=2', [
            11 => ['Post 11', 'https://example.test/blog/post-11'],
        ]),
        Schema::breadcrumbs([
            ['Home', 'https://example.test'],
            ['Blog', 'https://example.test/blog'],
            ['Testing', 'https://example.test/blog/category/testing?page=2'],
        ]),
    ]);
});
