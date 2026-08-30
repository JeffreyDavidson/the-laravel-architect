<?php

use App\Enums\ProjectStatus;
use App\Enums\PublishStatus;
use App\Models\Episode;
use App\Models\Podcast;
use App\Models\Post;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

beforeEach(function () {
    Http::fake([
        'www.googleapis.com/youtube/v3/channels*' => Http::response([
            'items' => [
                ['statistics' => ['subscriberCount' => 1234]],
            ],
        ]),
    ]);
});

it('provides a keyboard entry point and a programmatic newsletter label', function () {
    $page = visit(route('home', absolute: false));

    $page->assertAttribute('a[href="#main-content"]', 'href', '#main-content')
        ->assertAttribute('#main-content', 'tabindex', '-1')
        ->assertPresent('label[for="newsletter-email"]')
        ->assertAttribute('#newsletter-email', 'autocomplete', 'email')
        ->assertScript('document.querySelector("#newsletter-email").labels.length', 1)
        ->assertNoJavaScriptErrors();
});

it('announces testimonial validation once and associates field errors', function () {
    $page = visit(route('testimonials.create', absolute: false));

    $page->script('document.querySelector("form").noValidate = true');
    $page->press('Submit testimonial');

    $page->assertCount('[role="alert"][aria-live="assertive"]', 1)
        ->assertAttribute('#testimonial-name', 'aria-describedby', 'testimonial-name-error')
        ->assertAttribute('#testimonial-body', 'aria-describedby', 'testimonial-body-error')
        ->assertCount('#testimonial-name-error', 1)
        ->assertCount('#testimonial-body-error', 1)
        ->assertNoJavaScriptErrors();
});

it('uses valid description-list source order for project statistics', function () {
    Project::query()->create([
        'title' => 'Architecture Decisions',
        'slug' => 'architecture-decisions',
        'description' => 'A project shaped by explicit technical tradeoffs.',
        'tech_stack' => ['Laravel', 'Pest'],
        'is_featured' => true,
        'status' => ProjectStatus::Published,
    ]);

    $page = visit(route('projects.index', absolute: false));

    $page->assertCount('dl > div', 3)
        ->assertScript('Array.from(document.querySelectorAll("dl > div")).every((item) => item.children[0]?.tagName === "DT" && item.children[1]?.tagName === "DD")')
        ->assertNoJavaScriptErrors();
});

it('exposes podcast navigation, dates, and share actions to assistive technology', function () {
    $podcast = Podcast::query()->create([
        'name' => 'Architecture Sessions',
        'slug' => 'architecture-sessions',
        'description' => 'Conversations about maintainable Laravel applications.',
        'is_active' => true,
    ]);
    $episode = Episode::query()->create([
        'podcast_id' => $podcast->id,
        'title' => 'Designing Clear Boundaries',
        'slug' => 'designing-clear-boundaries',
        'episode_number' => 12,
        'season_number' => 1,
        'description' => 'A practical discussion about application boundaries.',
        'duration_minutes' => 42,
        'status' => PublishStatus::Published,
        'published_at' => '2026-08-20 12:00:00',
    ]);

    $page = visit(route('podcast.episode', [$podcast, $episode], absolute: false));

    $page->assertPresent('nav[aria-label="Breadcrumb"]')
        ->assertAttribute('nav[aria-label="Breadcrumb"] [aria-current="page"]', 'aria-current', 'page')
        ->assertCount('time[datetime="2026-08-20"]', 2)
        ->assertAttribute('a[aria-label="Share Designing Clear Boundaries on X"]', 'aria-label', 'Share Designing Clear Boundaries on X')
        ->assertAttribute('a[aria-label="Share Designing Clear Boundaries on LinkedIn"]', 'aria-label', 'Share Designing Clear Boundaries on LinkedIn')
        ->assertScript('Array.from(document.querySelectorAll("a.share-btn svg")).every((icon) => icon.getAttribute("aria-hidden") === "true")')
        ->assertNoJavaScriptErrors();
});

it('publishes machine-readable dates for articles', function () {
    $author = User::factory()->create();
    $post = Post::query()->create([
        'title' => 'Designing Clear Laravel Boundaries',
        'excerpt' => 'A focused guide to keeping Laravel applications maintainable.',
        'content' => 'Clear boundaries keep application behavior understandable.',
        'user_id' => $author->id,
        'status' => PublishStatus::Published,
        'published_at' => '2026-08-19 09:00:00',
    ]);

    $page = visit(route('blog.show', $post, absolute: false));

    $page->assertAttribute('time[datetime="2026-08-19"]', 'datetime', '2026-08-19')
        ->assertNoJavaScriptErrors();
});
