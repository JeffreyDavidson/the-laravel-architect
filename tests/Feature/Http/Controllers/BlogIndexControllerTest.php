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

it('uses stable item positions and page metadata for an unfiltered archive page', function () {
    $author = User::factory()->create();
    $publishedAt = now()->subDay();

    foreach (range(1, 13) as $index) {
        Post::query()->create([
            'title' => "Stable Article {$index}",
            'slug' => "stable-article-{$index}",
            'content' => 'A maintainable application starts with clear boundaries.',
            'user_id' => $author->getKey(),
            'status' => PublishStatus::Published,
            'published_at' => $publishedAt,
        ]);
    }

    $url = route('blog.index', ['page' => 2]);
    $content = $this->get($url)
        ->assertOk()
        ->assertSee('<title>Blog — Page 2 — Jeffrey Davidson</title>', false)
        ->assertSee('<link rel="canonical" href="'.$url.'">', false)
        ->assertSee('Stable Article 1', false)
        ->getContent();

    preg_match('/<script[^>]*type="application\/ld\+json"[^>]*>(.*?)<\/script>/s', $content, $matches);
    $structuredData = json_decode($matches[1] ?? '', true, flags: JSON_THROW_ON_ERROR);
    $itemList = collect($structuredData['@graph'])->firstWhere('@type', 'ItemList');

    expect($itemList['itemListElement'][0]['position'])->toBe(13);
});

it('rejects invalid public blog filters', function () {
    $this->get(route('blog.index', ['category' => 'missing-category']))
        ->assertNotFound();

    $this->get(route('blog.index', ['q' => str_repeat('x', 121)]))
        ->assertNotFound();
});

it('preserves a zero-valued search filter in canonical category links', function () {
    $author = User::factory()->create();
    $category = Category::query()->create([
        'name' => 'Laravel',
        'slug' => 'laravel',
    ]);

    Post::query()->create([
        'title' => 'Zero Search',
        'slug' => 'zero-search',
        'content' => 'A search term with a zero.',
        'category_id' => $category->getKey(),
        'user_id' => $author->getKey(),
        'status' => PublishStatus::Published,
        'published_at' => now()->subDay(),
    ]);

    $this->get(route('blog.index', ['q' => '0']))
        ->assertOk()
        ->assertSee('href="'.route('blog.index', ['q' => '0']).'"', false)
        ->assertSee('value="0"', false);
});
