<?php

use App\Models\Episode;
use App\Models\NewsletterIssue;
use App\Models\Podcast;
use App\Models\Post;
use App\Models\Project;
use App\Models\Video;
use App\Presenters\VideoPresenter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

use function Pest\Laravel\get;
use function Pest\Laravel\travelTo;

pest()->use(RefreshDatabase::class);

it('does not query unrelated content types for a filtered search', function () {
    DB::enableQueryLog();

    $response = get(route('search', ['q' => 'Architecture', 'type' => 'projects']));

    $response
        ->assertOk();

    $queries = collect(DB::getQueryLog())
        ->pluck('query')
        ->implode("\n");
    DB::disableQueryLog();

    expect($queries)->toContain('from "projects"')
        ->not
        ->toContain('from "posts"', 'from "podcasts"', 'from "episodes"', 'from "newsletter_issues"', 'from "videos"');
});

it('searches published content across every public content type', function () {
    $post = Post::factory()->published()
        ->create(['title' => 'Laravel Search Patterns']);
    $project = Project::factory()->published()
        ->create([
            'title' => 'Search Studio',
            'description' => 'A project built with Laravel.',
        ]);
    $podcast = Podcast::factory()->create(['name' => 'Laravel Conversations']);
    $episode = Episode::factory()->for($podcast)
        ->published()
        ->create(['title' => 'Searching with Laravel']);
    $video = Video::factory()->create(['title' => 'Laravel Search on YouTube']);
    Post::factory()->create(['title' => 'Private Laravel Search Notes']);

    $response = get(route('search', ['q' => 'Laravel']));

    $response
        ->assertOk()
        ->assertSee('5 results for')
        ->assertSeeHtml('>Laravel</mark> Search Patterns')
        ->assertSee(
            $project->title,
        )
        ->assertSeeHtml('>Laravel</mark> Conversations')
        ->assertSeeHtml('Searching with <mark')
        ->assertSeeHtml('>Laravel</mark> Search on YouTube')
        ->assertSeeHtml(route('blog.show', $post))
        ->assertSeeHtml(route('projects.show', $project))
        ->assertSeeHtml(route('podcasts.show', $podcast))
        ->assertSeeHtml(route('podcasts.episode', [$podcast, $episode]))
        ->assertSeeHtml(
            VideoPresenter::from($video)->youtubeUrl(),
        )
        ->assertDontSee('Private Laravel Search Notes')
        ->assertSeeHtml('<meta name="robots" content="noindex, follow">');
});

it('filters search results by content type and highlights matching text', function () {
    Project::factory()->published()
        ->create(['title' => 'Laravel Projects']);
    Post::factory()->published()
        ->create(['title' => 'Laravel Writing']);

    $response = get(route('search', ['q' => 'Laravel', 'type' => 'projects']));

    $response
        ->assertOk()
        ->assertSee('1 result for')
        ->assertSeeHtml('>Laravel</mark> Projects')
        ->assertSeeHtml('<mark class="rounded bg-brand-100 px-0.5 text-inherit dark:bg-brand-800">Laravel</mark>')
        ->assertDontSee('Laravel Writing')
        ->assertSeeHtml('name="type"');
});

it('escapes HTML in result titles and descriptions and keeps entities whole', function () {
    Project::factory()->published()
        ->create([
            'title' => '<b>Laravel</b> Q&A',
            'description' => '<script>alert(1)</script> Laravel answers',
        ]);

    $response = get(route('search', ['q' => 'Laravel']));
    $entityQueryResponse = get(route('search', ['q' => 'a']));

    $response->assertOk()
        ->assertSeeHtml('&lt;b&gt;<mark class="rounded bg-brand-100 px-0.5 text-inherit dark:bg-brand-800">Laravel</mark>&lt;/b&gt; Q&amp;A')
        ->assertDontSeeHtml('<b>Laravel</b>')
        ->assertDontSeeHtml('<script>alert(1)</script>');
    $entityQueryResponse->assertOk()
        ->assertSeeHtml('Q&amp;<mark class="rounded bg-brand-100 px-0.5 text-inherit dark:bg-brand-800">A</mark>')
        ->assertDontSeeHtml('&<mark');
});

it('dates results in the display timezone', function () {
    config(['app.display_timezone' => 'America/New_York']);
    travelTo('2026-10-10 12:00:00');
    Post::factory()
        ->published()
        ->create([
            'title' => 'Evening timezone post',
            'published_at' => '2026-10-06 01:00:00',
        ]);

    $response = get(route('search', ['q' => 'timezone']));

    $response
        ->assertOk()
        ->assertSeeHtml('<time datetime="2026-10-05">Oct 5, 2026</time>')
        ->assertDontSee('Oct 6, 2026');
});

it('rejects an unknown search content type', function () {
    $response = get(route('search', ['q' => 'Laravel', 'type' => 'unknown']));

    $response
        ->assertNotFound();
});

it('rate limits repeated searches from the same visitor', function () {
    foreach (range(1, 30) as $attempt) {
        get(route('search', ['q' => "Laravel {$attempt}"]))
            ->assertOk();
    }

    $response = get(route('search', ['q' => 'Laravel']));

    $response
        ->assertTooManyRequests();
});

it('does not rate limit the empty search page', function () {
    foreach (range(1, 31) as $attempt) {
        get(route('search'))
            ->assertOk();
    }
});

it('renders the empty search state', function () {
    $response = get(route('search'));

    $response->assertOk()
        ->assertSee('Search across the public archive');
});

it('rejects oversized queries', function () {
    $query = str_repeat('x', 121);

    $response = get(route('search', ['q' => $query]));

    $response->assertNotFound();
});

it('finds published newsletter issues', function () {
    $issue = NewsletterIssue::factory()->published()
        ->create(['excerpt' => 'A practical dispatching guide.']);

    $response = get(route('search', ['q' => 'dispatching']));

    $response
        ->assertOk()
        ->assertSee(
            $issue->title,
        )
        ->assertSeeHtml(route('newsletter.issue', $issue));
});

it('finds episodes by transcript content', function () {
    $podcast = Podcast::factory()->create();
    $episode = Episode::factory()->for($podcast)
        ->published()
        ->create(['transcript' => 'We explore event-driven Laravel systems.']);

    $response = get(route('search', ['q' => 'event-driven']));

    $response
        ->assertOk()
        ->assertSee(
            $episode->title,
        )
        ->assertSeeHtml(route('podcasts.episode', [$podcast, $episode]));
});

it('shows each group total and a link to the next page of a long group', function () {
    foreach (range(1, 13) as $number) {
        Post::factory()->published()
            ->create([
                'title' => "Paging post {$number}",
                'slug' => "paging-post-{$number}",
                'published_at' => now()->subMinutes($number),
            ]);
    }

    $response = get(route('search', ['q' => 'paging']));

    $response
        ->assertOk()
        ->assertSee('13 results for')
        ->assertSeeHtml('postsPage=2')
        ->assertSeeHtml(route('blog.show', 'paging-post-12').'"')
        ->assertDontSeeHtml(route('blog.show', 'paging-post-13').'"');
});
