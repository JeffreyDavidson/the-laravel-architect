<?php

use App\Enums\PublishStatus;
use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('serves RSS metadata with the correct media type', function () {
    $response = $this->get(route('rss'));

    $response
        ->assertOk()
        ->assertHeader('Content-Type', 'application/rss+xml; charset=UTF-8')
        ->assertSee('<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">', false)
        ->assertSee('<atom:link href="'.route('rss').'" rel="self" type="application/rss+xml" />', false);
});

it('includes only currently published posts and safely escapes feed content', function () {
    $author = User::factory()->create();
    $category = Category::query()->create([
        'name' => 'Architecture & Design',
        'slug' => 'architecture-design',
    ]);

    Post::query()->create([
        'title' => 'Laravel & PHP <Patterns>',
        'slug' => 'laravel-php-patterns',
        'excerpt' => 'Safe & useful <summary>',
        'content' => 'Published feed content.',
        'category_id' => $category->getKey(),
        'user_id' => $author->getKey(),
        'status' => PublishStatus::Published,
        'published_at' => now()->subHour(),
    ]);

    Post::query()->create([
        'title' => 'Draft feed post',
        'slug' => 'draft-feed-post',
        'content' => 'Draft content.',
        'user_id' => $author->getKey(),
        'status' => PublishStatus::Draft,
        'published_at' => now()->subDay(),
    ]);

    Post::query()->create([
        'title' => 'Future feed post',
        'slug' => 'future-feed-post',
        'content' => 'Scheduled content.',
        'user_id' => $author->getKey(),
        'status' => PublishStatus::Published,
        'published_at' => now()->addDay(),
    ]);

    $response = $this->get(route('rss'));

    $response
        ->assertOk()
        ->assertSee('<title>Laravel &amp; PHP &lt;Patterns&gt;</title>', false)
        ->assertSee('<description>Safe &amp; useful &lt;summary&gt;</description>', false)
        ->assertSee('<category>Architecture &amp; Design</category>', false)
        ->assertDontSee('Draft feed post', false)
        ->assertDontSee('Future feed post', false);
});
