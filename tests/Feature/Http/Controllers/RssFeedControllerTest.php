<?php

use App\Enums\PublishStatus;
use App\Models\Category;
use App\Models\NewsletterIssue;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\get;
use function Pest\Laravel\travelTo;

pest()->use(RefreshDatabase::class);

it('serves RSS metadata with the correct media type', function () {
    $response = $this->get(route('rss'));

    $response
        ->assertOk()
        ->assertHeader('Content-Type', 'application/rss+xml; charset=UTF-8')
        ->assertSeeHtml('<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">')
        ->assertSeeHtml('<atom:link href="'.route('rss').'" rel="self" type="application/rss+xml" />');
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

    $response->assertOk()
        ->assertSeeHtml('<title>Laravel &amp; PHP &lt;Patterns&gt;</title>')
        ->assertSeeHtml('<description>Safe &amp; useful &lt;summary&gt;</description>')
        ->assertSeeHtml('<category>Architecture &amp; Design</category>')
        ->assertDontSeeHtml('Draft feed post')
        ->assertDontSeeHtml('Future feed post');
});

it('serves the newsletter RSS feed with published issues only', function () {
    NewsletterIssue::query()->create([
        'title' => 'Laravel & PHP <Patterns>',
        'slug' => 'laravel-php-patterns',
        'excerpt' => 'Safe & useful <summary>',
        'content' => 'Published newsletter content.',
        'status' => PublishStatus::Published,
        'published_at' => now()->subHour(),
    ]);
    NewsletterIssue::query()->create([
        'title' => 'Draft newsletter issue',
        'slug' => 'draft-newsletter-issue',
        'excerpt' => 'Draft issue.',
        'content' => 'Draft newsletter content.',
        'status' => PublishStatus::Draft,
        'published_at' => now()->subDay(),
    ]);
    NewsletterIssue::query()->create([
        'title' => 'Future newsletter issue',
        'slug' => 'future-newsletter-issue',
        'excerpt' => 'Scheduled issue.',
        'content' => 'Future newsletter content.',
        'status' => PublishStatus::Published,
        'published_at' => now()->addDay(),
    ]);

    $response = $this->get(route('newsletter.rss'));

    $response
        ->assertOk()
        ->assertHeader('Content-Type', 'application/rss+xml; charset=UTF-8')
        ->assertSeeHtml('<atom:link href="'.route('newsletter.rss').'" rel="self" type="application/rss+xml" />')
        ->assertSeeHtml('<title>Laravel &amp; PHP &lt;Patterns&gt;</title>')
        ->assertSeeHtml('<description>Safe &amp; useful &lt;summary&gt;</description>')
        ->assertDontSeeHtml('Draft newsletter issue')
        ->assertDontSeeHtml('Future newsletter issue');
});

it('keeps the UTC publication instant in feed dates whatever the display timezone', function () {
    config(['app.display_timezone' => 'America/New_York']);
    travelTo('2026-10-10 12:00:00');
    $author = User::factory()->create();
    Post::query()->create([
        'title' => 'Evening post',
        'slug' => 'evening-post',
        'content' => 'Content.',
        'user_id' => $author->id,
        'status' => PublishStatus::Published,
        'published_at' => '2026-10-06 01:00:00',
    ]);

    get(route('rss'))
        ->assertOk()
        ->assertSeeHtml('<pubDate>Tue, 06 Oct 2026 01:00:00 +0000</pubDate>');
});

it('serves feeds to feed readers without starting a session or setting cookies', function (string $routeName) {
    config()->set('session.driver', 'database');

    $response = get(route($routeName));

    $response
        ->assertOk()
        ->assertHeaderMissing('Set-Cookie');
    expect($response->headers->getCookies())
        ->toBeEmpty();
    assertDatabaseCount('sessions', 0);
})->with([
    'site feed' => 'rss',
    'newsletter feed' => 'newsletter.rss',
]);
