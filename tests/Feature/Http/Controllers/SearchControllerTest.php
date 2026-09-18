<?php

use App\Enums\PublishStatus;
use App\Models\Episode;
use App\Models\NewsletterIssue;
use App\Models\Podcast;
use App\Models\Post;
use App\Models\Project;
use App\Models\User;
use App\Models\Video;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

pest()->use(RefreshDatabase::class);

it('does not query unrelated content types for a filtered search', function () {
    DB::enableQueryLog();

    $this->get(route('search', ['q' => 'Architecture', 'type' => 'projects']))->assertOk();

    $queries = collect(DB::getQueryLog())->pluck('query')->implode("\n");
    DB::disableQueryLog();

    expect($queries)->toContain('from "projects"')
        ->not->toContain('from "posts"', 'from "podcasts"', 'from "episodes"', 'from "newsletter_issues"', 'from "videos"');
});

it('searches published content across every public content type', function () {
    $author = User::factory()->create();
    $post = Post::query()->create([
        'title' => 'Laravel Search Patterns',
        'slug' => 'laravel-search-patterns',
        'excerpt' => 'A practical guide to searching a Laravel application.',
        'content' => 'Searchable content.',
        'user_id' => $author->id,
        'status' => PublishStatus::Published,
        'published_at' => now()->subDay(),
    ]);
    $project = Project::query()->create([
        'title' => 'Search Studio',
        'slug' => 'search-studio',
        'description' => 'A project built with Laravel.',
        'status' => PublishStatus::Published,
    ]);
    $podcast = Podcast::query()->create([
        'name' => 'Laravel Conversations',
        'slug' => 'laravel-conversations',
        'description' => 'A podcast about Laravel.',
        'is_active' => true,
    ]);
    $episode = Episode::query()->create([
        'podcast_id' => $podcast->id,
        'title' => 'Searching with Laravel',
        'slug' => 'searching-with-laravel',
        'description' => 'An episode about Laravel search.',
        'status' => PublishStatus::Published,
        'published_at' => now()->subDay(),
    ]);
    $video = Video::query()->create([
        'youtube_id' => 'search-video',
        'title' => 'Laravel Search on YouTube',
        'slug' => 'laravel-search-on-youtube',
        'description' => 'A Laravel search walkthrough.',
        'published_at' => now()->subDay(),
    ]);
    Post::query()->create([
        'title' => 'Private Laravel Search Notes',
        'slug' => 'private-laravel-search-notes',
        'content' => 'Draft content.',
        'user_id' => $author->id,
        'status' => PublishStatus::Draft,
    ]);

    $this->get(route('search', ['q' => 'Laravel']))
        ->assertOk()
        ->assertSee('5 results for')
        ->assertSeeHtml('>Laravel</mark> Search Patterns')
        ->assertSee($project->title)
        ->assertSeeHtml('>Laravel</mark> Conversations')
        ->assertSeeHtml('Searching with <mark')
        ->assertSeeHtml('>Laravel</mark> Search on YouTube')
        ->assertSeeHtml(route('blog.show', $post))
        ->assertSeeHtml(route('projects.show', $project))
        ->assertSeeHtml(route('podcast.show', $podcast))
        ->assertSeeHtml(route('podcast.episode', [$podcast, $episode]))
        ->assertSeeHtml($video->youtube_url)
        ->assertDontSee('Private Laravel Search Notes')
        ->assertSeeHtml('<meta name="robots" content="noindex, follow">');
});

it('filters search results by content type and highlights matching text', function () {
    $author = User::factory()->create();
    $project = Project::query()->create([
        'title' => 'Laravel Projects',
        'slug' => 'laravel-projects',
        'description' => 'A project about Laravel.',
        'status' => PublishStatus::Published,
    ]);
    Post::query()->create([
        'title' => 'Laravel Writing',
        'slug' => 'laravel-writing',
        'content' => 'Writing about Laravel.',
        'user_id' => $author->id,
        'status' => PublishStatus::Published,
        'published_at' => now()->subDay(),
    ]);

    $this->get(route('search', ['q' => 'Laravel', 'type' => 'projects']))
        ->assertOk()
        ->assertSee('1 result for')
        ->assertSeeHtml('>Laravel</mark> Projects')
        ->assertSeeHtml('<mark class="rounded bg-brand-100 px-0.5 text-inherit dark:bg-brand-800">Laravel</mark>')
        ->assertDontSee('Laravel Writing')
        ->assertSeeHtml('name="type"');
});

it('rejects an unknown search content type', function () {
    $this->get(route('search', ['q' => 'Laravel', 'type' => 'unknown']))->assertNotFound();
});

it('renders the empty search state and rejects oversized queries', function () {
    $this->get(route('search'))->assertOk()->assertSee('Search across the public archive');

    $this->get(route('search', ['q' => str_repeat('x', 121)]))->assertNotFound();
});

it('finds published newsletter issues', function () {
    $issue = NewsletterIssue::query()->create([
        'title' => 'Newsletter Issue About Queues',
        'slug' => 'newsletter-issue-about-queues',
        'excerpt' => 'A practical dispatching guide.',
        'content' => 'Reliable queue workers for Laravel applications.',
        'status' => PublishStatus::Published,
        'published_at' => now()->subDay(),
    ]);

    $this->get(route('search', ['q' => 'dispatching']))
        ->assertOk()
        ->assertSee($issue->title)
        ->assertSeeHtml(route('newsletter.issue', $issue));
});

it('finds episodes by transcript content', function () {
    $podcast = Podcast::query()->create([
        'name' => 'Architecture Sessions',
        'slug' => 'architecture-sessions',
        'description' => 'Conversations about Laravel architecture.',
        'is_active' => true,
    ]);
    $episode = Episode::query()->create([
        'podcast_id' => $podcast->id,
        'title' => 'A Conversation About Boundaries',
        'slug' => 'a-conversation-about-boundaries',
        'description' => 'A practical architecture discussion.',
        'transcript' => 'We explore event-driven Laravel systems.',
        'status' => PublishStatus::Published,
        'published_at' => now()->subDay(),
    ]);

    $this->get(route('search', ['q' => 'event-driven']))
        ->assertOk()
        ->assertSee($episode->title)
        ->assertSeeHtml(route('podcast.episode', [$podcast, $episode]));
});
