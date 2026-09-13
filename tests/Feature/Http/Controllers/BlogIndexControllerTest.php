<?php

use App\Enums\PublishStatus;
use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('renders validated search filters with pagination metadata and accessible result status', function () {
    $author = User::factory()->create();
    $category = Category::query()->create([
        'name' => 'Laravel',
        'slug' => 'laravel',
    ]);

    foreach (range(1, 13) as $index) {
        Post::query()->create([
            'title' => "Laravel Article {$index}",
            'slug' => "laravel-article-{$index}",
            'excerpt' => 'A practical guide.',
            'content' => 'A maintainable application starts with clear boundaries.',
            'category_id' => $category->getKey(),
            'user_id' => $author->getKey(),
            'status' => PublishStatus::Published,
            'published_at' => now()->subDays($index),
        ]);
    }

    $url = route('blog.index', ['q' => 'Laravel', 'category' => 'laravel', 'page' => 2]);

    $this->get($url)
        ->assertOk()
        ->assertSee('Showing 13–13 of 13 articles.', false)
        ->assertSee('name="q"', false)
        ->assertSee('value="Laravel"', false)
        ->assertSee('name="category"', false)
        ->assertSee('<meta name="robots" content="noindex, follow">', false)
        ->assertSee('<link rel="canonical" href="'.route('blog.index', ['category' => 'laravel']).'">', false)
        ->assertSee('Read article:', false);
});

it('returns not found for an out-of-range public blog page', function () {
    $author = User::factory()->create();

    Post::query()->create([
        'title' => 'Only Article',
        'slug' => 'only-article',
        'content' => 'A maintainable application starts with clear boundaries.',
        'user_id' => $author->getKey(),
        'status' => PublishStatus::Published,
        'published_at' => now()->subDay(),
    ]);

    $this->get(route('blog.index', ['page' => 2]))
        ->assertNotFound();
});

it('rejects invalid public blog filters', function () {
    $this->get(route('blog.index', ['category' => 'missing-category']))
        ->assertNotFound();
});
