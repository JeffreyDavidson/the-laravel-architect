<?php

use App\Enums\PublishStatus;
use App\Enums\SearchContentType;
use App\Models\NewsletterIssue;
use App\Models\Post;
use App\Models\User;
use App\Queries\SearchQuery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

use function Pest\Laravel\travelTo;

pest()->use(RefreshDatabase::class);

/**
 * Create published posts and newsletter issues whose titles all match "paging".
 */
function seedPagedSearchContent(int $posts, int $issues): void
{
    $author = User::factory()->create();

    foreach (range(1, $posts) as $number) {
        Post::query()->create([
            'title' => "Paging post {$number}",
            'slug' => "paging-post-{$number}",
            'content' => 'Content.',
            'user_id' => $author->id,
            'status' => PublishStatus::Published,
            'published_at' => now()->subMinutes($number),
        ]);
    }

    foreach (range(1, $issues) as $number) {
        NewsletterIssue::query()->create([
            'title' => "Paging issue {$number}",
            'slug' => "paging-issue-{$number}",
            'content' => 'Content.',
            'status' => PublishStatus::Published,
            'published_at' => now()->subMinutes($number),
        ]);
    }
}

/**
 * Run the search as if the given query string had been requested.
 *
 * @param  array<string, string>  $parameters
 * @return array<string, LengthAwarePaginator<int, array{title: string, description: string|null, url: string, meta: string, external: bool}>>
 */
function searchWithParameters(array $parameters, ?SearchContentType $type = null): array
{
    app()->instance('request', Request::create(route('search', $parameters)));

    return app(SearchQuery::class)->get($parameters['q'], $type);
}

it('dates results in the display timezone', function () {
    config(['app.display_timezone' => 'America/New_York']);
    travelTo('2026-10-10 12:00:00');
    $author = User::factory()->create();
    Post::query()->create([
        'title' => 'Evening timezone post',
        'slug' => 'evening-timezone-post',
        'content' => 'Content.',
        'user_id' => $author->id,
        'status' => PublishStatus::Published,
        'published_at' => '2026-10-06 01:00:00',
    ]);

    $results = searchWithParameters(['q' => 'timezone']);

    expect($results['Writing']->first())
        ->toMatchArray(['meta' => 'Oct 5, 2026']);
});

it('pages each result group with its own total', function () {
    seedPagedSearchContent(posts: 13, issues: 2);

    $firstPage = searchWithParameters(['q' => 'paging']);
    $secondPage = searchWithParameters(['q' => 'paging', 'postsPage' => '2']);

    expect($firstPage['Writing']->count())
        ->toBe(12)
        ->and($firstPage['Writing']->total())
        ->toBe(13)
        ->and($secondPage['Writing']->count())
        ->toBe(1)
        ->and($secondPage['Writing']->currentPage())
        ->toBe(2);
});

it('keeps the other groups on their first page when one group is paged', function () {
    seedPagedSearchContent(posts: 13, issues: 2);

    $results = searchWithParameters(['q' => 'paging', 'postsPage' => '2']);

    expect($results['Newsletter']->currentPage())
        ->toBe(1)
        ->and($results['Newsletter']->count())
        ->toBe(2);
});

it('keeps the search terms and returns to the group heading in page links', function () {
    seedPagedSearchContent(posts: 13, issues: 0);

    $results = searchWithParameters(['q' => 'paging', 'type' => 'writing'], SearchContentType::Writing);
    $nextPageUrl = $results['Writing']->nextPageUrl();

    expect($nextPageUrl)
        ->toContain('q=paging', 'type=writing', 'postsPage=2')
        ->toEndWith('#search-writing');
});

it('uses a distinct page parameter for every group', function () {
    seedPagedSearchContent(posts: 1, issues: 1);

    $results = searchWithParameters(['q' => 'paging']);
    $pageNames = array_map(fn (LengthAwarePaginator $paginator): string => $paginator->getPageName(), $results);

    expect($pageNames)
        ->toBe([
            'Writing' => 'postsPage',
            'Projects' => 'projectsPage',
            'Podcasts' => 'podcastsPage',
            'Newsletter' => 'newsletterPage',
            'Episodes' => 'episodesPage',
            'Videos' => 'videosPage',
        ]);
});

it('reads the page size for every group from configuration', function () {
    config()->set('search.per_page', 5);
    seedPagedSearchContent(posts: 7, issues: 0);

    $results = searchWithParameters(['q' => 'paging']);

    expect($results['Writing']->count())
        ->toBe(5)
        ->and($results['Writing']->perPage())
        ->toBe(5)
        ->and($results['Writing']->total())
        ->toBe(7);
});
