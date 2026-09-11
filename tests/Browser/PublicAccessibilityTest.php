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

it('keeps newsletter validation accessible and preserves the submitted email', function () {
    $page = visit(route('home', absolute: false));

    $page->script('document.querySelector("#newsletter-email").form.noValidate = true');
    $page->fill('Email address', 'not-an-email');
    $page->press('Subscribe');

    $page->assertPresent('#newsletter-email-error')
        ->assertValue('#newsletter-email', 'not-an-email')
        ->assertAttribute('#newsletter-email', 'aria-invalid', 'true')
        ->assertAttribute('#newsletter-email', 'aria-describedby', 'newsletter-email-error newsletter-privacy');
});

it('gives project entries a heading and a labeled technology list', function () {
    Project::query()->create([
        'title' => 'Architecture Decisions',
        'slug' => 'architecture-decisions',
        'description' => 'A project shaped by explicit technical tradeoffs.',
        'tech_stack' => ['Laravel', 'Pest'],
        'is_featured' => true,
        'status' => ProjectStatus::Published,
    ]);

    $page = visit(route('projects.index', absolute: false));

    $page->assertCount('[data-project-entry]', 1)
        ->assertSeeIn('[data-project-entry] h3', 'Architecture Decisions')
        ->assertAttribute('[data-project-entry] ul', 'aria-label', 'Technologies used for Architecture Decisions')
        ->assertCount('[data-project-entry] ul > li', 2)
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
