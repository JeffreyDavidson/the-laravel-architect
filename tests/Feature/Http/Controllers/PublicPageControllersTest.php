<?php

use App\Models\Category;
use App\Models\Podcast;
use App\Models\Post;
use App\Models\Project;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\get;

pest()->use(RefreshDatabase::class);

require_once __DIR__.'/../../../Support/PublicPageAssertions.php';

it('renders the core public pages', function (string $uri, string $copy) {
    $response = get($uri);

    $response
        ->assertOk()
        ->assertSeeHtml($copy);
})->with([
    ['/', 'The Laravel Architect'],
    ['/about', 'About'],
    ['/contact', 'Contact'],
    ['/privacy', 'Privacy'],
    ['/uses', 'Uses'],
    ['/blog', 'Blog'],
    ['/projects', 'Projects'],
    ['/podcasts', 'Podcast'],
    ['/archive', 'Archive'],
]);

it('renders canonical structured data for static public pages', function (string $routeName, string $type, string $name) {
    $url = route($routeName);
    $response = get($url);

    $response
        ->assertOk();

    $content = $response->getContent();

    $structuredData = decodeStructuredData($content);
    $graph = structuredDataGraph($structuredData['@graph'] ?? null);
    $page = structuredDataObject(collect($graph)->firstWhere('@type', $type));

    expect($page)->toMatchArray([
        '@id' => $url.'#page',
        'name' => $name,
        'url' => $url,
        'isPartOf' => [
            '@type' => 'WebSite',
            '@id' => route('home').'#website',
        ],
    ]);

})->with([
    'home' => ['home', 'WebPage', 'The Laravel Architect'],
    'contact' => ['contact.create', 'ContactPage', 'Contact'],
    'privacy' => ['privacy', 'WebPage', 'Privacy'],
    'uses' => ['uses', 'WebPage', 'Uses'],
]);

it('renders canonical structured data for public content collections', function (string $collection) {
    // Arrange
    $category = Category::factory()->create(['name' => 'Architecture']);
    $tag = Tag::factory()->create(['name' => 'Boundaries']);
    $post = Post::factory()->for($category)
        ->published()
        ->create();
    $post->attachTag($tag);
    $project = Project::factory()->published()
        ->create();
    $podcast = Podcast::factory()->create();

    $collections = [
        'blog' => [route('blog.index'), 'Blog', $post->title, route('blog.show', $post)],
        'category' => [route('blog.category', $category), 'Architecture Articles', $post->title, route('blog.show', $post)],
        'tag' => [route('blog.tag', $tag), 'Articles Tagged Boundaries', $post->title, route('blog.show', $post)],
        'projects' => [route('projects.index'), 'Projects', $project->title, route('projects.show', $project)],
        'podcasts' => [route('podcasts.index'), 'Podcast', $podcast->name, route('podcasts.show', $podcast)],
    ];
    [$url, $name, $itemName, $itemUrl] = $collections[$collection];

    // Act
    $response = get($url);

    // Assert
    $response
        ->assertOk();

    $structuredData = decodeStructuredData($response->getContent());
    $graph = structuredDataGraph($structuredData['@graph'] ?? null);
    $collectionPage = structuredDataObject(collect($graph)->firstWhere('@type', 'CollectionPage'));
    $itemList = structuredDataObject(collect($graph)->firstWhere('@type', 'ItemList'));

    expect($collectionPage)
        ->toMatchArray([
            '@id' => $url.'#collection',
            'name' => $name,
            'url' => $url,
            'mainEntity' => [
                '@type' => 'ItemList',
                '@id' => $url.'#items',
            ],
        ])
        ->and($itemList)
        ->toMatchArray([
            '@id' => $url.'#items',
            'numberOfItems' => 1,
            'itemListElement' => [[
                '@type' => 'ListItem',
                'position' => 1,
                'name' => $itemName,
                'item' => $itemUrl,
            ]],
        ]);
})->with(['blog', 'category', 'tag', 'projects', 'podcasts']);

it('uses page-specific metadata for paginated taxonomy archives', function (string $routeName, string $taxonomy, string $title, string $description) {
    // Arrange
    $category = Category::factory()->create(['name' => 'Architecture']);
    $tag = Tag::factory()->create(['name' => 'Boundaries']);

    foreach (range(1, 11) as $index) {
        $post = Post::factory()->for($category)
            ->published()
            ->create(['published_at' => now()->subDays($index)]);
        $post->attachTag($tag);
    }

    $taxonomies = ['category' => $category, 'tag' => $tag];
    $url = route($routeName, [$taxonomy => $taxonomies[$taxonomy], 'page' => 2]);

    // Act
    $response = get($url);

    // Assert
    $response
        ->assertOk()
        ->assertSeeHtml('<title>'.$title.'</title>')
        ->assertSeeHtml('<meta name="description" content="'.$description.'">')
        ->assertSeeHtml('<link rel="canonical" href="'.$url.'">')
        ->assertSeeHtml('<meta property="og:url" content="'.$url.'">');

    $structuredData = decodeStructuredData($response->getContent());
    $graph = structuredDataGraph($structuredData['@graph'] ?? null);
    $collectionPage = structuredDataObject(collect($graph)->firstWhere('@type', 'CollectionPage'));
    $itemList = structuredDataObject(collect($graph)->firstWhere('@type', 'ItemList'));
    $breadcrumbList = structuredDataObject(collect($graph)->firstWhere('@type', 'BreadcrumbList'));

    expect($collectionPage)
        ->toMatchArray([
            '@id' => $url.'#collection',
            'url' => $url,
        ])
        ->and($itemList)
        ->toMatchArray([
            '@id' => $url.'#items',
        ])
        ->and(structuredDataListItem($itemList['itemListElement'] ?? null, 0))
        ->toMatchArray([
            '@type' => 'ListItem',
            'position' => 11,
        ])
        ->and(structuredDataListItem($breadcrumbList['itemListElement'] ?? null, 2))
        ->toMatchArray([
            '@type' => 'ListItem',
            'position' => 3,
            'item' => $url,
        ]);
})->with([
    'category' => [
        'blog.category',
        'category',
        'Architecture Articles — Page 2 — Jeffrey Davidson',
        'Articles about Architecture — Laravel development insights from Jeffrey Davidson. Page 2 of 2.',
    ],
    'tag' => [
        'blog.tag',
        'tag',
        'Articles Tagged Boundaries — Page 2 — Jeffrey Davidson',
        'Articles tagged with Boundaries on The Laravel Architect. Page 2 of 2.',
    ],
]);

it('returns not found for out-of-range taxonomy archive pages', function (string $routeName, string $taxonomy) {
    // Arrange
    $category = Category::factory()->create();
    $tag = Tag::factory()->create();
    $post = Post::factory()->for($category)
        ->published()
        ->create();
    $post->attachTag($tag);

    $taxonomies = ['category' => $category, 'tag' => $tag];

    // Act
    $response = get(route($routeName, [$taxonomy => $taxonomies[$taxonomy], 'page' => 2]));

    // Assert
    $response
        ->assertNotFound();
})->with([
    'category' => ['blog.category', 'category'],
    'tag' => ['blog.tag', 'tag'],
]);

it('keeps one main landmark on public index pages', function (string $routeName) {
    $response = get(route($routeName));

    $response
        ->assertOk();

    $content = responseContent($response->getContent());

    expect(substr_count($content, '<main'))->toBe(1)
        ->and(substr_count($content, '</main>'))
        ->toBe(1);
})->with([
    'projects' => 'projects.index',
    'podcasts' => 'podcasts.index',
    'newsletter' => 'newsletter.index',
    'archive' => 'archive.index',
]);

it('provides a valid legacy favicon fallback', function () {
    $favicon = file_get_contents(public_path('favicon.ico'));

    $favicon = responseContent($favicon);

    expect(strlen($favicon))->toBeGreaterThan(0)
        ->and(substr($favicon, 0, 4))
        ->toBe("\x00\x00\x01\x00");
});
