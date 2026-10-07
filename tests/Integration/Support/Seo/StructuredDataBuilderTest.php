<?php

use App\Models\Category;
use App\Models\Episode;
use App\Models\Podcast;
use App\Models\Post;
use App\Models\Project;
use App\Models\Tag;
use App\Support\Seo\StructuredDataBuilder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Date;
use RalphJSmit\Laravel\SEO\Support\SEOData;

use function Pest\Laravel\travelTo;

pest()->use(RefreshDatabase::class);

/** @return array<string, mixed> */
function seoBuilderWebsite(): array
{
    return [
        '@type' => 'WebSite',
        '@id' => 'http://localhost#website',
        'name' => 'The Laravel Architect',
        'url' => 'http://localhost',
        'author' => [
            '@type' => 'Person',
            '@id' => 'http://localhost/about#person',
            'name' => 'Jeffrey Davidson',
            'url' => 'http://localhost/about',
        ],
    ];
}

/**
 * @param  list<array{0: string, 1: string}>  $items
 * @return array<string, mixed>
 */
function seoBuilderBreadcrumbs(array $items): array
{
    return [
        '@type' => 'BreadcrumbList',
        'itemListElement' => array_map(
            fn (array $item, int $index): array => [
                '@type' => 'ListItem',
                'position' => $index + 1,
                'name' => $item[0],
                'item' => $item[1],
            ],
            $items,
            array_keys($items),
        ),
    ];
}

/**
 * @param  array<int, array{0: string, 1: string}>  $itemsByPosition
 * @return list<array<string, mixed>>
 */
function seoBuilderCollection(string $name, string $url, array $itemsByPosition): array
{
    $elements = [];

    foreach ($itemsByPosition as $position => $item) {
        $elements[] = [
            '@type' => 'ListItem',
            'position' => $position,
            'name' => $item[0],
            'item' => $item[1],
        ];
    }

    return [
        [
            '@type' => 'CollectionPage',
            '@id' => "{$url}#collection",
            'name' => $name,
            'url' => $url,
            'mainEntity' => [
                '@type' => 'ItemList',
                '@id' => "{$url}#items",
            ],
        ],
        [
            '@type' => 'ItemList',
            '@id' => "{$url}#items",
            'numberOfItems' => count($elements),
            'itemListElement' => $elements,
        ],
    ];
}

/**
 * @template TValue
 *
 * @param  list<TValue>  $items
 * @return LengthAwarePaginator<int, TValue>
 */
function seoBuilderPage(array $items, int $total, int $perPage, int $currentPage): LengthAwarePaginator
{
    return new LengthAwarePaginator($items, $total, $perPage, $currentPage);
}

/**
 * @param  array<string, mixed>  $pageData
 * @return list<array<string, mixed>>
 */
function buildStructuredData(array $pageData, string $routeName): array
{
    return app(StructuredDataBuilder::class)
        ->build($pageData, $routeName);
}

it('describes static pages with their page type and breadcrumbs', function (string $routeName, string $type, string $name, string $path, bool $isProfile) {
    $url = "http://localhost{$path}";
    $page = [
        '@type' => $type,
        '@id' => "{$url}#page",
        'name' => $name,
        'url' => $url,
        'isPartOf' => [
            '@type' => 'WebSite',
            '@id' => 'http://localhost#website',
        ],
    ];

    if ($isProfile) {
        $page['mainEntity'] = [
            '@type' => 'Person',
            '@id' => 'http://localhost/about#person',
        ];
    }

    $schemas = buildStructuredData([], $routeName);

    expect($schemas)->toBe([
        seoBuilderWebsite(),
        $page,
        seoBuilderBreadcrumbs([['Home', 'http://localhost'], [$name, $url]]),
    ]);
})->with([
    'about' => ['about', 'ProfilePage', 'About', '/about', true],
    'contact' => ['contact.create', 'ContactPage', 'Contact', '/contact', false],
    'privacy' => ['privacy', 'WebPage', 'Privacy', '/privacy', false],
    'uses' => ['uses', 'WebPage', 'Uses', '/uses', false],
]);

it('describes the home page without breadcrumbs', function () {
    $schemas = buildStructuredData([], 'home');

    expect($schemas)->toBe([
        seoBuilderWebsite(),
        [
            '@type' => 'WebPage',
            '@id' => 'http://localhost#page',
            'name' => 'The Laravel Architect',
            'url' => 'http://localhost',
            'isPartOf' => [
                '@type' => 'WebSite',
                '@id' => 'http://localhost#website',
            ],
        ],
    ]);
});

it('only describes the website on pages without their own schema', function (string $routeName) {
    $schemas = buildStructuredData([
        'seoSource' => new SEOData(canonical_url: 'https://canonical.test/ignored'),
    ], $routeName);

    expect($schemas)->toBe([seoBuilderWebsite()]);
})->with([
    'newsletter index' => 'newsletter.index',
    'newsletter issue' => 'newsletter.issue',
    'search' => 'search',
    'services' => 'services',
    'not found page' => '',
]);

it('skips model schemas and breadcrumbs when the page model is missing', function (string $routeName) {
    $schemas = buildStructuredData([], $routeName);

    expect($schemas)->toBe([seoBuilderWebsite()]);
})->with([
    'post' => 'blog.show',
    'category' => 'blog.category',
    'tag' => 'blog.tag',
    'project' => 'projects.show',
    'podcast' => 'podcast.show',
    'episode' => 'podcast.episode',
]);

it('lists the first blog page from position one under the canonical url', function () {
    $first = Post::factory()->create(['title' => 'First post', 'slug' => 'first-post']);
    $second = Post::factory()->create(['title' => 'Second post', 'slug' => 'second-post']);

    $schemas = buildStructuredData([
        'posts' => seoBuilderPage([$first, $second], 5, 2, 1),
        'selectedCategory' => null,
        'seoSource' => new SEOData(canonical_url: 'https://canonical.test/blog'),
    ], 'blog.index');

    expect($schemas)->toBe([
        seoBuilderWebsite(),
        ...seoBuilderCollection('Blog', 'https://canonical.test/blog', [
            1 => ['First post', 'http://localhost/blog/first-post'],
            2 => ['Second post', 'http://localhost/blog/second-post'],
        ]),
        seoBuilderBreadcrumbs([
            ['Home', 'http://localhost'],
            ['Blog', 'https://canonical.test/blog'],
        ]),
    ]);
});

it('offsets later blog pages and names a selected category', function () {
    $category = Category::factory()->create(['name' => 'Testing', 'slug' => 'testing']);
    $third = Post::factory()->create(['title' => 'Third post', 'slug' => 'third-post']);
    $fourth = Post::factory()->create(['title' => 'Fourth post', 'slug' => 'fourth-post']);

    $schemas = buildStructuredData([
        'posts' => seoBuilderPage([$third, $fourth], 5, 2, 2),
        'selectedCategory' => $category,
        'seoSource' => new SEOData(canonical_url: 'https://canonical.test/blog?category=testing&page=2'),
    ], 'blog.index');

    expect($schemas)->toBe([
        seoBuilderWebsite(),
        ...seoBuilderCollection('Testing Articles', 'https://canonical.test/blog?category=testing&page=2', [
            3 => ['Third post', 'http://localhost/blog/third-post'],
            4 => ['Fourth post', 'http://localhost/blog/fourth-post'],
        ]),
        seoBuilderBreadcrumbs([
            ['Home', 'http://localhost'],
            ['Blog', 'https://canonical.test/blog?category=testing&page=2'],
        ]),
    ]);
});

it('falls back to the route url when the blog index has no canonical url', function () {
    $schemas = buildStructuredData([
        'posts' => seoBuilderPage([], 0, 2, 1),
        'seoSource' => new SEOData,
    ], 'blog.index');

    expect($schemas)->toBe([
        seoBuilderWebsite(),
        ...seoBuilderCollection('Blog', 'http://localhost/blog', []),
        seoBuilderBreadcrumbs([
            ['Home', 'http://localhost'],
            ['Blog', 'http://localhost/blog'],
        ]),
    ]);
});

it('describes a post as an article with blog breadcrumbs', function () {
    travelTo(Date::parse('2026-09-02 10:00:00'));
    $post = Post::factory()->create([
        'title' => 'Shipping Laravel',
        'slug' => 'shipping-laravel',
        'excerpt' => 'How it ships.',
        'published_at' => Date::parse('2026-09-01 08:30:00'),
    ]);

    $schemas = buildStructuredData([
        'post' => $post,
        'seoSource' => new SEOData(canonical_url: 'https://canonical.test/ignored'),
    ], 'blog.show');

    expect($schemas)->toBe([
        seoBuilderWebsite(),
        [
            '@type' => 'Article',
            '@id' => 'http://localhost/blog/shipping-laravel#article',
            'url' => 'http://localhost/blog/shipping-laravel',
            'headline' => 'Shipping Laravel',
            'datePublished' => '2026-09-01T08:30:00+00:00',
            'dateModified' => '2026-09-02T10:00:00+00:00',
            'author' => [
                '@type' => 'Person',
                '@id' => 'http://localhost/about#person',
            ],
            'mainEntityOfPage' => 'http://localhost/blog/shipping-laravel',
            'description' => 'How it ships.',
            'image' => 'http://localhost/og-image/shipping-laravel',
        ],
        seoBuilderBreadcrumbs([
            ['Home', 'http://localhost'],
            ['Blog', 'http://localhost/blog'],
            ['Shipping Laravel', 'http://localhost/blog/shipping-laravel'],
        ]),
    ]);
});

it('offsets a paginated category listing', function () {
    $category = Category::factory()->create(['name' => 'Testing', 'slug' => 'testing']);
    $post = Post::factory()->create(['title' => 'Fifth post', 'slug' => 'fifth-post']);

    $schemas = buildStructuredData([
        'category' => $category,
        'posts' => seoBuilderPage([$post], 5, 2, 3),
        'seoSource' => new SEOData(canonical_url: 'https://canonical.test/blog/category/testing?page=3'),
    ], 'blog.category');

    expect($schemas)->toBe([
        seoBuilderWebsite(),
        ...seoBuilderCollection('Testing Articles', 'https://canonical.test/blog/category/testing?page=3', [
            5 => ['Fifth post', 'http://localhost/blog/fifth-post'],
        ]),
        seoBuilderBreadcrumbs([
            ['Home', 'http://localhost'],
            ['Blog', 'http://localhost/blog'],
            ['Testing', 'https://canonical.test/blog/category/testing?page=3'],
        ]),
    ]);
});

it('offsets a paginated tag listing', function () {
    $tag = Tag::factory()->create(['name' => 'livewire']);
    $post = Post::factory()->create(['title' => 'Third post', 'slug' => 'third-post']);

    $schemas = buildStructuredData([
        'tag' => $tag,
        'posts' => seoBuilderPage([$post], 3, 2, 2),
        'seoSource' => new SEOData,
    ], 'blog.tag');

    expect($schemas)->toBe([
        seoBuilderWebsite(),
        ...seoBuilderCollection('Articles Tagged livewire', 'http://localhost/blog/tag/livewire', [
            3 => ['Third post', 'http://localhost/blog/third-post'],
        ]),
        seoBuilderBreadcrumbs([
            ['Home', 'http://localhost'],
            ['Blog', 'http://localhost/blog'],
            ['livewire', 'http://localhost/blog/tag/livewire'],
        ]),
    ]);
});

it('offsets a paginated archive listing', function () {
    $schemas = buildStructuredData([
        'items' => seoBuilderPage([
            ['title' => 'An episode', 'url' => 'http://localhost/podcasts/show/an-episode'],
            ['title' => 'A post', 'url' => 'http://localhost/blog/a-post'],
        ], 30, 20, 2),
        'seoSource' => new SEOData(canonical_url: 'https://canonical.test/archive?page=2'),
    ], 'archive.index');

    expect($schemas)->toBe([
        seoBuilderWebsite(),
        ...seoBuilderCollection('Archive', 'https://canonical.test/archive?page=2', [
            21 => ['An episode', 'http://localhost/podcasts/show/an-episode'],
            22 => ['A post', 'http://localhost/blog/a-post'],
        ]),
        seoBuilderBreadcrumbs([
            ['Home', 'http://localhost'],
            ['Archive', 'https://canonical.test/archive?page=2'],
        ]),
    ]);
});

it('lists every project on the project index', function () {
    $first = Project::factory()->create(['title' => 'First project', 'slug' => 'first-project']);
    $second = Project::factory()->create(['title' => 'Second project', 'slug' => 'second-project']);

    $schemas = buildStructuredData([
        'projects' => new EloquentCollection([$first, $second]),
        'seoSource' => new SEOData(canonical_url: 'https://canonical.test/ignored'),
    ], 'projects.index');

    expect($schemas)->toBe([
        seoBuilderWebsite(),
        ...seoBuilderCollection('Projects', 'http://localhost/projects', [
            1 => ['First project', 'http://localhost/projects/first-project'],
            2 => ['Second project', 'http://localhost/projects/second-project'],
        ]),
        seoBuilderBreadcrumbs([
            ['Home', 'http://localhost'],
            ['Projects', 'http://localhost/projects'],
        ]),
    ]);
});

it('describes a project case study with its technologies and link', function () {
    $project = Project::factory()->create([
        'title' => 'Ringside',
        'slug' => 'ringside',
        'description' => 'Wrestling promotion software.',
        'tech_stack' => ['Laravel', 'Livewire'],
        'url' => 'https://ringside.test',
    ]);

    $schemas = buildStructuredData([
        'project' => $project,
        'seoSource' => $project,
    ], 'projects.show');

    expect($schemas)->toBe([
        seoBuilderWebsite(),
        [
            '@type' => 'CreativeWork',
            '@id' => 'http://localhost/projects/ringside#project',
            'name' => 'Ringside',
            'url' => 'http://localhost/projects/ringside',
            'mainEntityOfPage' => 'http://localhost/projects/ringside',
            'description' => 'Wrestling promotion software.',
            'author' => [
                '@type' => 'Person',
                '@id' => 'http://localhost/about#person',
            ],
            'keywords' => 'Laravel, Livewire',
            'sameAs' => ['https://ringside.test'],
        ],
        seoBuilderBreadcrumbs([
            ['Home', 'http://localhost'],
            ['Projects', 'http://localhost/projects'],
            ['Ringside', 'http://localhost/projects/ringside'],
        ]),
    ]);
});

it('lists the active podcast on the podcast index', function () {
    $podcast = Podcast::factory()->create(['name' => 'Coffee Chat', 'slug' => 'coffee-chat']);

    $schemas = buildStructuredData([
        'podcast' => $podcast,
        'seoSource' => new SEOData,
    ], 'podcast.index');

    expect($schemas)->toBe([
        seoBuilderWebsite(),
        ...seoBuilderCollection('Podcast', 'http://localhost/podcasts', [
            1 => ['Coffee Chat', 'http://localhost/podcasts/coffee-chat'],
        ]),
        seoBuilderBreadcrumbs([
            ['Home', 'http://localhost'],
            ['Podcast', 'http://localhost/podcasts'],
        ]),
    ]);
});

it('keeps an empty podcast index listing when no podcast is active', function () {
    $schemas = buildStructuredData([
        'podcast' => null,
        'seoSource' => new SEOData,
    ], 'podcast.index');

    expect($schemas)->toBe([
        seoBuilderWebsite(),
        ...seoBuilderCollection('Podcast', 'http://localhost/podcasts', []),
        seoBuilderBreadcrumbs([
            ['Home', 'http://localhost'],
            ['Podcast', 'http://localhost/podcasts'],
        ]),
    ]);
});

it('describes a podcast series and offsets its paginated episodes', function () {
    $podcast = Podcast::factory()->create([
        'name' => 'Coffee Chat',
        'slug' => 'coffee-chat',
        'description' => 'A weekly chat.',
    ]);
    $episode = Episode::factory()
        ->for($podcast)
        ->create(['title' => 'Episode eleven', 'slug' => 'episode-eleven']);

    $schemas = buildStructuredData([
        'podcast' => $podcast,
        'episodes' => seoBuilderPage([$episode], 11, 10, 2),
        'seoSource' => new SEOData(canonical_url: 'https://canonical.test/podcasts/coffee-chat?page=2'),
    ], 'podcast.show');

    expect($schemas)->toBe([
        seoBuilderWebsite(),
        [
            '@type' => 'PodcastSeries',
            '@id' => 'http://localhost/podcasts/coffee-chat#podcast',
            'name' => 'Coffee Chat',
            'url' => 'http://localhost/podcasts/coffee-chat',
            'author' => [
                '@type' => 'Person',
                '@id' => 'http://localhost/about#person',
            ],
            'description' => 'A weekly chat.',
        ],
        ...seoBuilderCollection('Coffee Chat Episodes', 'https://canonical.test/podcasts/coffee-chat?page=2', [
            11 => ['Episode eleven', 'http://localhost/podcasts/coffee-chat/episode-eleven'],
        ]),
        seoBuilderBreadcrumbs([
            ['Home', 'http://localhost'],
            ['Podcast', 'http://localhost/podcasts'],
            ['Coffee Chat', 'https://canonical.test/podcasts/coffee-chat?page=2'],
        ]),
    ]);
});

it('describes an episode within its series with three-level breadcrumbs', function () {
    $podcast = Podcast::factory()->create([
        'name' => 'Coffee Chat',
        'slug' => 'coffee-chat',
        'description' => '',
    ]);
    $episode = Episode::factory()
        ->for($podcast)
        ->create([
            'title' => 'Pilot',
            'slug' => 'pilot',
            'description' => 'The first one.',
            'published_at' => Date::parse('2026-08-28 12:00:00'),
            'episode_number' => 1,
            'duration_seconds' => 3725,
        ]);

    $schemas = buildStructuredData([
        'podcast' => $podcast,
        'episode' => $episode,
        'seoSource' => $episode,
    ], 'podcast.episode');

    expect($schemas)->toBe([
        seoBuilderWebsite(),
        [
            '@type' => 'PodcastSeries',
            '@id' => 'http://localhost/podcasts/coffee-chat#podcast',
            'name' => 'Coffee Chat',
            'url' => 'http://localhost/podcasts/coffee-chat',
            'author' => [
                '@type' => 'Person',
                '@id' => 'http://localhost/about#person',
            ],
        ],
        [
            '@type' => 'PodcastEpisode',
            '@id' => 'http://localhost/podcasts/coffee-chat/pilot#episode',
            'name' => 'Pilot',
            'url' => 'http://localhost/podcasts/coffee-chat/pilot',
            'mainEntityOfPage' => 'http://localhost/podcasts/coffee-chat/pilot',
            'partOfSeries' => [
                '@type' => 'PodcastSeries',
                '@id' => 'http://localhost/podcasts/coffee-chat#podcast',
            ],
            'description' => 'The first one.',
            'datePublished' => '2026-08-28T12:00:00+00:00',
            'episodeNumber' => 1,
            'duration' => 'PT1H2M5S',
        ],
        seoBuilderBreadcrumbs([
            ['Home', 'http://localhost'],
            ['Podcast', 'http://localhost/podcasts'],
            ['Coffee Chat', 'http://localhost/podcasts/coffee-chat'],
            ['Pilot', 'http://localhost/podcasts/coffee-chat/pilot'],
        ]),
    ]);
});
