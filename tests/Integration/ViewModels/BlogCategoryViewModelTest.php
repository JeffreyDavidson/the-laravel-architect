<?php

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use App\ViewModels\BlogCategoryViewModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

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

    expect($data)->toHaveKeys(['category', 'posts', 'seoSource'])
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
        ->and($data['seoSource']->title)
        ->toBe('Architecture Articles — Page 2')
        ->and($data['seoSource']->description)
        ->toBe(
            'Articles about Architecture — Laravel development insights from Jeffrey Davidson. Page 2 of 2.',
        )
        ->and($data['seoSource']->url)
        ->toBe($canonicalUrl)
        ->and($data['seoSource']->canonical_url)
        ->toBe($canonicalUrl);
});
