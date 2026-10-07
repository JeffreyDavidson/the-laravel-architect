<?php

use App\Enums\SearchContentType;
use App\Models\Episode;
use App\Models\NewsletterIssue;
use App\Models\Podcast;
use App\Models\Post;
use App\Models\Project;
use App\Models\Tag;
use App\Models\User;
use App\Models\Video;
use App\Queries\SearchQuery;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
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
        Post::factory()
            ->for($author, 'author')
            ->published()
            ->create([
                'title' => "Paging post {$number}",
                'published_at' => now()->subMinutes($number),
            ]);
    }

    foreach (range(1, $issues) as $number) {
        NewsletterIssue::factory()
            ->published()
            ->create([
                'title' => "Paging issue {$number}",
                'published_at' => now()->subMinutes($number),
            ]);
    }
}

/**
 * Run the search as if the given query string had been requested.
 *
 * @param  array<string, string>  $parameters
 * @return array<string, LengthAwarePaginator<int, Post>|LengthAwarePaginator<int, Project>|LengthAwarePaginator<int, Podcast>|LengthAwarePaginator<int, NewsletterIssue>|LengthAwarePaginator<int, Episode>|LengthAwarePaginator<int, Video>>
 */
function searchWithParameters(array $parameters, ?SearchContentType $type = null): array
{
    app()->instance('request', Request::create(route('search', $parameters)));

    return app(SearchQuery::class)->get($parameters['q'], $type);
}

/**
 * Count the matches in each result group, with every group present and zero by default.
 *
 * @param  array<string, LengthAwarePaginator<int, Post>|LengthAwarePaginator<int, Project>|LengthAwarePaginator<int, Podcast>|LengthAwarePaginator<int, NewsletterIssue>|LengthAwarePaginator<int, Episode>|LengthAwarePaginator<int, Video>>  $results
 * @return array<string, int>
 */
function searchTotals(array $results): array
{
    $totals = array_fill_keys(array_keys(SearchContentType::labels()), 0);

    foreach ($results as $group => $paginator) {
        $totals[$group] = $paginator->total();
    }

    return $totals;
}

it('returns the matching published models with their raw publication dates', function () {
    $post = Post::factory()
        ->published()
        ->create([
            'title' => 'Evening timezone post',
            'published_at' => '2026-10-06 01:00:00',
        ]);

    $results = searchWithParameters(['q' => 'timezone']);
    $result = $results['writing']->first();

    if (! $result instanceof Post) {
        throw new RuntimeException('Expected the matching post.');
    }

    expect($result->is($post))->toBeTrue()
        ->and($result->published_at?->toDateTimeString())
        ->toBe('2026-10-06 01:00:00');
});

it('pages each result group with its own total', function () {
    seedPagedSearchContent(posts: 13, issues: 2);

    $firstPage = searchWithParameters(['q' => 'paging']);
    $secondPage = searchWithParameters(['q' => 'paging', 'postsPage' => '2']);

    expect($firstPage['writing']->count())
        ->toBe(12)
        ->and($firstPage['writing']->total())
        ->toBe(13)
        ->and($secondPage['writing']->count())
        ->toBe(1)
        ->and($secondPage['writing']->currentPage())
        ->toBe(2);
});

it('keeps the other groups on their first page when one group is paged', function () {
    seedPagedSearchContent(posts: 13, issues: 2);

    $results = searchWithParameters(['q' => 'paging', 'postsPage' => '2']);

    expect($results['newsletter']->currentPage())
        ->toBe(1)
        ->and($results['newsletter']->count())
        ->toBe(2);
});

it('keeps the search terms and returns to the group heading in page links', function () {
    seedPagedSearchContent(posts: 13, issues: 0);

    $results = searchWithParameters(['q' => 'paging', 'type' => 'writing'], SearchContentType::Writing);
    $nextPageUrl = $results['writing']->nextPageUrl();

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
            'writing' => 'postsPage',
            'projects' => 'projectsPage',
            'podcasts' => 'podcastsPage',
            'newsletter' => 'newsletterPage',
            'episodes' => 'episodesPage',
            'videos' => 'videosPage',
        ]);
});

it('reads the page size for every group from configuration', function () {
    config()->set('search.per_page', 5);
    seedPagedSearchContent(posts: 7, issues: 0);

    $results = searchWithParameters(['q' => 'paging']);

    expect($results['writing']->count())
        ->toBe(5)
        ->and($results['writing']->perPage())
        ->toBe(5)
        ->and($results['writing']->total())
        ->toBe(7);
});

it('finds published content by every searched column', function (Closure $factory, array $attributes, string $group) {
    /** @var Closure(): Factory<Model> $factory */
    /** @var array<string, string> $attributes */
    $factory()
        ->create($attributes);

    $results = searchWithParameters(['q' => 'zephyrquill']);

    expect(searchTotals($results))
        ->toBe([...searchTotals([]), $group => 1]);
})->with([
    'post title' => [fn (): Factory => Post::factory()->published(), ['title' => 'A Zephyrquill post'], 'writing'],
    'post excerpt' => [fn (): Factory => Post::factory()->published(), ['excerpt' => 'A zephyrquill excerpt'], 'writing'],
    'post content' => [fn (): Factory => Post::factory()->published(), ['content' => '<p>A zephyrquill body</p>'], 'writing'],
    'project title' => [fn (): Factory => Project::factory()->published(), ['title' => 'Zephyrquill studio'], 'projects'],
    'project description' => [fn (): Factory => Project::factory()->published(), ['description' => 'A zephyrquill tool'], 'projects'],
    'project content' => [fn (): Factory => Project::factory()->published(), ['content' => 'Built around zephyrquill.'], 'projects'],
    'podcast name' => [fn (): Factory => Podcast::factory(), ['name' => 'Zephyrquill radio'], 'podcasts'],
    'podcast description' => [fn (): Factory => Podcast::factory(), ['description' => 'A zephyrquill show'], 'podcasts'],
    'podcast long description' => [fn (): Factory => Podcast::factory(), ['long_description' => 'All about zephyrquill.'], 'podcasts'],
    'newsletter title' => [fn (): Factory => NewsletterIssue::factory()->published(), ['title' => 'Zephyrquill weekly'], 'newsletter'],
    'newsletter excerpt' => [fn (): Factory => NewsletterIssue::factory()->published(), ['excerpt' => 'A zephyrquill note'], 'newsletter'],
    'newsletter content' => [fn (): Factory => NewsletterIssue::factory()->published(), ['content' => 'More zephyrquill.'], 'newsletter'],
    'episode title' => [fn (): Factory => Episode::factory()->published(), ['title' => 'Zephyrquill episode'], 'episodes'],
    'episode description' => [fn (): Factory => Episode::factory()->published(), ['description' => 'A zephyrquill chat'], 'episodes'],
    'episode show notes' => [fn (): Factory => Episode::factory()->published(), ['show_notes' => 'Links on zephyrquill.'], 'episodes'],
    'episode transcript' => [fn (): Factory => Episode::factory()->published(), ['transcript' => 'We talk zephyrquill.'], 'episodes'],
    'episode guest name' => [fn (): Factory => Episode::factory()->published(), ['guest_name' => 'Ada Zephyrquill'], 'episodes'],
    'video title' => [fn (): Factory => Video::factory(), ['title' => 'Zephyrquill on video'], 'videos'],
    'video description' => [fn (): Factory => Video::factory(), ['description' => 'A zephyrquill demo'], 'videos'],
]);

it('finds published posts by tag name', function () {
    $post = Post::factory()
        ->published()
        ->create();
    $post->attachTag(Tag::factory()->create(['name' => 'Zephyrquill']));

    $results = searchWithParameters(['q' => 'zephyrquill']);

    expect(searchTotals($results))
        ->toBe([...searchTotals([]), 'writing' => 1]);
});

it('matches percent signs and underscores in the query literally', function (string $query, string $matchingTitle, string $wildcardTitle) {
    Post::factory()
        ->published()
        ->create(['title' => $matchingTitle]);
    Post::factory()
        ->published()
        ->create(['title' => $wildcardTitle]);

    $results = searchWithParameters(['q' => $query]);
    $titles = $results['writing']
        ->getCollection()
        ->pluck('title')
        ->all();

    expect($titles)->toBe([$matchingTitle]);
})->with([
    'percent sign' => ['100%', 'Save 100% today', 'Save 1000 today'],
    'underscore' => ['snake_case', 'Using snake_case keys', 'Using snakeXcase keys'],
]);

it('returns only the requested group for a filtered search', function (SearchContentType $type) {
    $results = searchWithParameters(['q' => 'anything'], $type);

    expect(array_keys($results))->toBe([$type->value]);
})->with(SearchContentType::cases());
