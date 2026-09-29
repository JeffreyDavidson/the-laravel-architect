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

it('pages each result group with its own total', function () {
    seedPagedSearchContent(posts: 13, issues: 2);

    $firstPage = searchWithParameters(['q' => 'paging']);
    $secondPage = searchWithParameters(['q' => 'paging', 'postsPage' => '2']);

    expect($firstPage['Writing']->count())
        ->toBe(SearchQuery::PER_PAGE)
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
