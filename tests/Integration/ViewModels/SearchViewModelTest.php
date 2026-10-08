<?php

use App\Enums\SearchContentType;
use App\Models\Episode;
use App\Models\NewsletterIssue;
use App\Models\Podcast;
use App\Models\Post;
use App\Models\Project;
use App\Models\Video;
use App\ViewModels\SearchViewModel;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;

pest()->use(RefreshDatabase::class);

/**
 * Search for the query and return the first result the ViewModel builds for one content type.
 *
 * @return array{title: string, description: string|null, url: string, date: CarbonInterface|null, label: string, external: bool}
 */
function firstSearchViewModelResult(string $query, SearchContentType $type): array
{
    $data = app(SearchViewModel::class)
        ->data($query, $type);
    $result = $data['results'][$type->value]->items()[0] ?? null;

    if ($result === null) {
        throw new RuntimeException("Expected a {$type->value} search result.");
    }

    return $result;
}

it('turns each matching model into a search result', function (SearchContentType $type, Closure $createModel, Closure $expectedUrl, string $label, bool $dated, bool $external) {
    /** @var Closure(): Model $createModel */
    /** @var Closure(Model): string $expectedUrl */
    $model = $createModel();

    $result = firstSearchViewModelResult('Zephyrquill', $type);

    expect($result['title'])->toBe('Zephyrquill result')
        ->and($result['url'])
        ->toBe($expectedUrl($model))
        ->and($result['label'])
        ->toBe($label)
        ->and($result['date'] instanceof CarbonInterface)
        ->toBe($dated)
        ->and($result['external'])
        ->toBe($external);
})->with([
    'post' => [
        SearchContentType::Writing,
        fn (): Post => Post::factory()
            ->published()
            ->create(['title' => 'Zephyrquill result']),
        fn (Post $post): string => route('blog.show', $post),
        'Article',
        true,
        false,
    ],
    'project' => [
        SearchContentType::Projects,
        fn (): Project => Project::factory()
            ->published()
            ->create(['title' => 'Zephyrquill result']),
        fn (Project $project): string => route('projects.show', $project),
        'Project',
        false,
        false,
    ],
    'podcast' => [
        SearchContentType::Podcasts,
        fn (): Podcast => Podcast::factory()->create(['name' => 'Zephyrquill result']),
        fn (Podcast $podcast): string => route('podcasts.show', $podcast),
        'Podcast',
        false,
        false,
    ],
    'newsletter issue' => [
        SearchContentType::Newsletter,
        fn (): NewsletterIssue => NewsletterIssue::factory()
            ->published()
            ->create(['title' => 'Zephyrquill result']),
        fn (NewsletterIssue $issue): string => route('newsletter.issue', $issue),
        'Newsletter',
        true,
        false,
    ],
    'episode' => [
        SearchContentType::Episodes,
        fn (): Episode => Episode::factory()
            ->published()
            ->create(['title' => 'Zephyrquill result']),
        fn (Episode $episode): string => route('podcasts.episode', [$episode->podcast, $episode]),
        'Episode',
        true,
        false,
    ],
    'video' => [
        SearchContentType::Videos,
        fn (): Video => Video::factory()->create(['title' => 'Zephyrquill result', 'youtube_id' => 'abc123']),
        fn (): string => 'https://www.youtube.com/watch?v=abc123',
        'YouTube video',
        false,
        true,
    ],
]);

it('shows result descriptions as plain text cut to 180 characters', function () {
    Post::factory()
        ->published()
        ->create([
            'title' => 'Zephyrquill result',
            'excerpt' => '<p>Q&A <tips> "Laravel"</p>'.str_repeat('x', 200),
        ]);

    $result = firstSearchViewModelResult('Zephyrquill', SearchContentType::Writing);

    expect($result['description'])
        ->toStartWith('Q&A  "Laravel"x')
        ->toEndWith('...')
        ->toHaveLength(183);
});

it('leaves out the description of a result without one', function () {
    Video::factory()->create([
        'title' => 'Zephyrquill result',
        'description' => null,
    ]);

    $result = firstSearchViewModelResult('Zephyrquill', SearchContentType::Videos);

    expect($result['description'])->toBeNull();
});

it('keeps the search terms and returns to the group heading in page links', function () {
    foreach (range(1, 13) as $number) {
        Post::factory()
            ->published()
            ->create(['title' => "Paging post {$number}"]);
    }
    app()->instance('request', Request::create(route('search', ['q' => 'paging', 'type' => 'writing'])));

    $data = app(SearchViewModel::class)->data('paging', SearchContentType::Writing);

    expect($data['results']['writing']->nextPageUrl())
        ->toContain('q=paging', 'type=writing', 'postsPage=2')
        ->toEndWith('#search-writing');
});
