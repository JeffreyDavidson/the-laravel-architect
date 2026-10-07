<?php

use App\Models\Category;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

use function Pest\Laravel\get;

pest()->use(RefreshDatabase::class);

it('renders validated search filters with pagination metadata and accessible result status', function () {
    $category = Category::factory()->create([
        'name' => 'Laravel',
        'slug' => 'laravel',
    ]);

    foreach (range(1, 13) as $index) {
        Post::factory()->for($category)
            ->published()
            ->create([
                'title' => "Laravel Article {$index}",
                'published_at' => now()->subDays($index),
            ]);
    }

    $url = route('blog.index', ['q' => 'Laravel', 'category' => 'laravel', 'page' => 2]);

    DB::enableQueryLog();

    get($url)
        ->assertOk()
        ->assertSeeHtml('Showing 13–13 of 13 articles.')
        ->assertSeeHtml('name="q"')
        ->assertSeeHtml('value="Laravel"')
        ->assertSeeHtml('name="category"')
        ->assertSeeHtml('<meta name="robots" content="noindex, follow">')
        ->assertSeeHtml('<link rel="canonical" href="'.route('blog.category', $category).'">')
        ->assertSeeHtml('Read article:');

    $queries = collect(DB::getQueryLog());
    DB::disableQueryLog();

    expect($queries->filter(fn (array $query): bool => str_contains($query['query'], 'from "posts"') && str_contains($query['query'], 'limit 12'))
        ->count())->toBe(1);
});

it('returns not found for an out-of-range public blog page', function () {
    Post::factory()->published()
        ->create();

    get(route('blog.index', ['page' => 2]))
        ->assertNotFound();
});

it('uses stable item positions and page metadata for an unfiltered archive page', function () {
    $publishedAt = now()->subDay();

    foreach (range(1, 13) as $index) {
        Post::factory()->published()
            ->create([
                'title' => "Stable Article {$index}",
                'published_at' => $publishedAt,
            ]);
    }

    $url = route('blog.index', ['page' => 2]);
    $content = get($url)
        ->assertOk()
        ->assertSeeHtml('<title>Blog — Page 2 — Jeffrey Davidson</title>')
        ->assertSeeHtml('<link rel="canonical" href="'.$url.'">')
        ->assertSeeHtml('Stable Article 1')
        ->getContent();

    if (! is_string($content)) {
        throw new RuntimeException('Expected the blog response to contain HTML.');
    }

    if (
        preg_match('/<script[^>]*type="application\/ld\+json"[^>]*>(.*?)<\/script>/s', $content, $matches) !== 1
    ) {
        throw new RuntimeException('Expected the blog response to contain JSON-LD.');
    }

    $structuredData = json_decode($matches[1], true, flags: JSON_THROW_ON_ERROR);

    if (! is_array($structuredData) || ! isset($structuredData['@graph']) || ! is_array($structuredData['@graph'])) {
        throw new RuntimeException('Expected JSON-LD graph data.');
    }
    $itemList = array_find($structuredData['@graph'], fn ($graphItem) => is_array($graphItem) && ($graphItem['@type'] ?? null) === 'ItemList');

    if (
        ! is_array($itemList)
        || ! isset($itemList['itemListElement'])
        || ! is_array($itemList['itemListElement'])
        || ! isset($itemList['itemListElement'][0])
        || ! is_array($itemList['itemListElement'][0])
    ) {
        throw new RuntimeException('Expected JSON-LD item list data.');
    }

    expect($itemList['itemListElement'][0]['position'] ?? null)->toBe(13);
});

it('points a category-filtered blog page at its category archive', function (array $filters) {
    $category = Category::factory()->create([
        'name' => 'Laravel',
        'slug' => 'laravel',
    ]);

    Post::factory()->count(13)
        ->for($category)
        ->published()
        ->create();

    $response = get(route('blog.index', ['category' => 'laravel', ...$filters]));

    $response->assertSeeHtml('<link rel="canonical" href="'.route('blog.category', $category).'">')
        ->assertSeeHtml('<meta property="og:url" content="'.route('blog.category', $category).'">');
})->with([
    'first page' => [[]],
    'later page' => [['page' => 2]],
]);

it('rejects invalid public blog filters', function () {
    get(route('blog.index', ['category' => 'missing-category']))
        ->assertNotFound();

    get(route('blog.index', ['q' => str_repeat('x', 121)]))
        ->assertNotFound();
});

it('preserves a zero-valued search filter in canonical category links', function () {
    Post::factory()->published()
        ->create();

    get(route('blog.index', ['q' => '0']))
        ->assertOk()
        ->assertSeeHtml('href="'.route('blog.index', ['q' => '0']).'"')
        ->assertSeeHtml('value="0"');
});
