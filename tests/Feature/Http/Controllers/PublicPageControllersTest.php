<?php

use App\Models\Category;
use App\Models\Episode;
use App\Models\Podcast;
use App\Models\Post;
use App\Models\Project;
use App\Models\Tag;
use App\Models\Video;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Vite;

use function Pest\Laravel\get;
use function Pest\Laravel\travelTo;
use function Pest\Laravel\withVite;

pest()->use(RefreshDatabase::class);

/**
 * @return array<array-key, mixed>
 */
function decodeStructuredData(string|false $content): array
{
    if (! is_string($content)) {
        throw new RuntimeException('The response did not contain HTML content.');
    }

    if (preg_match('/<script[^>]*type="application\/ld\+json"[^>]*>(.*?)<\/script>/s', $content, $matches) !== 1) {
        throw new RuntimeException('The response did not contain structured data.');
    }

    $json = $matches[1];

    $structuredData = json_decode($json, true, flags: JSON_THROW_ON_ERROR);

    if (! is_array($structuredData)) {
        throw new RuntimeException('The structured data was not an object.');
    }

    return $structuredData;
}

/**
 * @return list<array<array-key, mixed>>
 */
function structuredDataGraph(mixed $value): array
{
    if (! is_array($value)) {
        throw new RuntimeException('The structured data graph was not an array.');
    }

    $graph = [];

    foreach ($value as $item) {
        if (! is_array($item)) {
            throw new RuntimeException('The structured data graph contained an invalid item.');
        }

        $graph[] = $item;
    }

    return $graph;
}

/**
 * @return array<array-key, mixed>
 */
function structuredDataObject(mixed $value): array
{
    if (! is_array($value)) {
        throw new RuntimeException('The structured data item was not an object.');
    }

    return $value;
}

/**
 * @return array<array-key, mixed>
 */
function structuredDataListItem(mixed $value, int $index): array
{
    if (! is_array($value)) {
        throw new RuntimeException('The structured data list was not an array.');
    }

    return structuredDataObject($value[$index] ?? null);
}

/**
 * @return array<string, array{file: string}>
 */
function assetManifest(): array
{
    $content = file_get_contents(public_path('build/manifest.json'));

    if (! is_string($content)) {
        throw new RuntimeException('The Vite manifest could not be read.');
    }

    $decoded = json_decode($content, true, flags: JSON_THROW_ON_ERROR);

    if (! is_array($decoded)) {
        throw new RuntimeException('The Vite manifest was not an object.');
    }

    $manifest = [];

    foreach ($decoded as $key => $entry) {
        if (! is_string($key) || ! is_array($entry) || ! is_string($entry['file'] ?? null)) {
            throw new RuntimeException('The Vite manifest contained an invalid entry.');
        }

        $manifest[$key] = ['file' => $entry['file']];
    }

    return $manifest;
}

function responseContent(string|false $content): string
{
    if (! is_string($content)) {
        throw new RuntimeException('The response did not contain HTML content.');
    }

    return $content;
}

function stringPosition(string $haystack, string $needle): int
{
    $position = strpos($haystack, $needle);

    if ($position === false) {
        throw new RuntimeException("The response did not contain {$needle}.");
    }

    return $position;
}

function configuredString(mixed $value): string
{
    if (! is_string($value) && ! is_int($value) && ! is_float($value)) {
        throw new RuntimeException('The configured value was not a string.');
    }

    return (string) $value;
}

beforeEach(function () {
    Http::fake([
        'www.googleapis.com/youtube/v3/channels*' => Http::response([
            'items' => [
                ['statistics' => ['subscriberCount' => 1234]],
            ],
        ]),
    ]);
});

it('renders the core public pages', function (string $uri, string $copy) {
    get($uri)
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

it('renders page-specific SEO metadata', function () {
    get(route('about'))
        ->assertOk()
        ->assertSeeHtml('<title>About — Jeffrey Davidson</title>')
        ->assertSeeHtml('<meta name="description" content="Meet Jeffrey Davidson — 15+ years of PHP experience, Laravel architect, podcaster, and dad. Building clean, maintainable applications and sharing the journey.">');
});

it('shares pages with a territory-specific Open Graph locale', function () {
    get(route('home'))
        ->assertOk()
        ->assertSeeHtml('<meta property="og:locale" content="en_US">');
});

it('names the mobile theme toggle by its visible label', function () {
    $content = responseContent(get(route('home'))
        ->assertOk()
        ->getContent());

    preg_match('/<button[^>]*theme-toggle-mobile[^>]*>/', $content, $mobileToggle);

    expect($mobileToggle[0] ?? '')
        ->toContain('data-theme-toggle')
        ->not->toContain('aria-label')
        ->and(substr_count($content, 'aria-label="Toggle theme"'))
        ->toBe(1);
});

it('gives tag and category archives with the same name distinct titles', function () {
    $category = Category::factory()->create(['name' => 'Laravel']);
    $tag = Tag::factory()->create(['name' => 'Laravel']);

    get(route('blog.category', $category))
        ->assertOk()
        ->assertSeeHtml('<title>Laravel Articles — Jeffrey Davidson</title>');
    get(route('blog.tag', $tag))
        ->assertOk()
        ->assertSeeHtml('<title>Articles Tagged Laravel — Jeffrey Davidson</title>');
});

it('renders model-specific SEO metadata', function () {
    $post = Post::factory()->published()
        ->create([
            'title' => 'Designing Clear Laravel Boundaries',
            'excerpt' => 'A focused guide to keeping Laravel applications maintainable.',
        ]);

    get(route('blog.show', $post))
        ->assertOk()
        ->assertSeeHtml('<title>Designing Clear Laravel Boundaries — Jeffrey Davidson</title>')
        ->assertSeeHtml('<meta name="description" content="A focused guide to keeping Laravel applications maintainable.">');
});

it('shares blog posts as articles with their publication times', function () {
    travelTo(Date::parse('2026-09-01 12:00:00'));
    $post = Post::factory()->published()
        ->create([
            'published_at' => Date::parse('2026-08-19 09:30:00'),
        ]);

    $response = get(route('blog.show', $post));

    $response->assertSeeHtml('<meta property="og:type" content="article">')
        ->assertSeeHtml('<meta property="article:published_time" content="2026-08-19T09:30:00+00:00">')
        ->assertSeeHtml('<meta property="article:modified_time" content="2026-09-01T12:00:00+00:00">');
});

it('shares one wide article image in social cards and structured data', function (string $slug, ?string $featuredImagePath, Closure $expectedImage) {
    withVite();
    Storage::fake('public', ['url' => config('filesystems.disks.public.url')]);
    $post = Post::factory()->published()
        ->create([
            'slug' => $slug,
            'featured_image_path' => $featuredImagePath,
        ]);
    $image = configuredString($expectedImage($post));

    $content = responseContent(get(route('blog.show', $post))
        ->getContent());

    $graph = structuredDataGraph(decodeStructuredData($content)['@graph'] ?? null);
    $article = structuredDataObject(collect($graph)->firstWhere('@type', 'Article'));
    expect($content)
        ->toContain("<meta property=\"og:image\" content=\"{$image}\">")
        ->toContain('<meta name="twitter:card" content="summary_large_image">')
        ->not->toContain('logo-color-black-bg.png')
        ->and($article['image'] ?? null)
        ->toBe($image);
})->with([
    'uploaded featured image' => [
        'uploaded-image-article',
        'posts/article.png',
        fn (Post $post): ?string => $post->featured_image_url,
    ],
    'bundled artwork' => [
        'hello-world-why-im-starting-this-blog',
        null,
        fn (): string => Vite::asset('resources/images/post-hello-world-1280.webp'),
    ],
    'no artwork' => [
        'article-without-artwork',
        null,
        fn (Post $post): string => route('og-image', $post),
    ],
]);

it('renders bundled editorial artwork and article navigation for seeded posts', function () {
    withVite();

    $posts = [
        'hello-world-why-im-starting-this-blog' => 'post-hello-world-768',
        'from-kansas-to-florida-a-developers-journey' => 'post-kansas-florida-768',
        'what-15-years-of-web-development-taught-me' => 'home-writing-modules-768',
        'why-i-still-choose-laravel-in-2026' => 'home-writing-review-768',
        'how-i-structure-every-laravel-project' => 'home-writing-fallback-768',
    ];

    foreach ($posts as $slug => $asset) {
        $post = Post::factory()->published()
            ->create([
                'slug' => $slug,
                'content' => "## A Starting Point\n\nStart with Laravel.\n\n## Thin Controllers\n\nKeep the boundary clear.",
            ]);

        get(route('blog.show', $post))
            ->assertOk()
            ->assertSeeHtml("data-post-artwork=\"{$slug}\"")
            ->assertSeeHtml($asset)
            ->assertSeeHtml(str_replace('-768', '-384', $asset))
            ->assertSeeHtml('data-article-toc');
    }

    $blog = get(route('blog.index'))
        ->assertOk();

    foreach (array_keys($posts) as $slug) {
        $blog->assertSeeHtml("data-post-artwork=\"{$slug}\"");
    }
});

it('renders canonical structured data for the site and blog posts', function () {
    $post = Post::factory()->published()
        ->create();

    $content = get(route('blog.show', $post))
        ->assertOk()
        ->getContent();

    $structuredData = decodeStructuredData($content);

    expect($structuredData['@context'] ?? null)->toBe('https://schema.org');

    $graph = structuredDataGraph($structuredData['@graph'] ?? null);
    $website = structuredDataObject(collect($graph)->firstWhere('@type', 'WebSite'));
    $article = structuredDataObject(collect($graph)->firstWhere('@type', 'Article'));

    expect($website)
        ->toMatchArray([
            '@id' => route('home').'#website',
            'url' => route('home'),
        ])
        ->and($website['author'])
        ->toMatchArray([
            '@id' => route('about').'#person',
            'url' => route('about'),
        ])
        ->and($article)
        ->toMatchArray([
            '@id' => route('blog.show', $post).'#article',
            'url' => route('blog.show', $post),
            'mainEntityOfPage' => route('blog.show', $post),
            'author' => [
                '@type' => 'Person',
                '@id' => route('about').'#person',
            ],
        ]);
});

it('keeps a post title from closing the structured data script', function () {
    $title = '</script><h1>x';
    $post = Post::factory()->published()
        ->create(['title' => $title]);

    $content = get(route('blog.show', $post))
        ->assertDontSeeHtml('</script><h1>')
        ->getContent();

    $graph = structuredDataGraph(decodeStructuredData($content)['@graph'] ?? null);
    $article = structuredDataObject(collect($graph)->firstWhere('@type', 'Article'));

    expect($article['headline'] ?? null)
        ->toBe($title);
});

it('renders canonical structured data for static public pages', function (string $routeName, string $type, string $name) {
    $url = route($routeName);
    $content = get($url)
        ->assertOk()
        ->getContent();

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

    if ($routeName === 'about') {
        expect($page['mainEntity'])->toMatchArray([
            '@type' => 'Person',
            '@id' => route('about').'#person',
        ]);
    }
})->with([
    'home' => ['home', 'WebPage', 'The Laravel Architect'],
    'about' => ['about', 'ProfilePage', 'About'],
    'contact' => ['contact.create', 'ContactPage', 'Contact'],
    'privacy' => ['privacy', 'WebPage', 'Privacy'],
    'uses' => ['uses', 'WebPage', 'Uses'],
]);

it('renders canonical structured data for podcasts and episodes', function () {
    $podcast = Podcast::factory()->create();
    $episode = Episode::factory()->for($podcast)
        ->published()
        ->create([
            'episode_number' => 12,
            'duration_seconds' => 3725,
        ]);

    $content = get(route('podcast.episode', [$podcast, $episode]))
        ->assertOk()
        ->getContent();

    $structuredData = decodeStructuredData($content);
    $graph = structuredDataGraph($structuredData['@graph'] ?? null);
    $podcastSeries = structuredDataObject(collect($graph)->firstWhere('@type', 'PodcastSeries'));
    $podcastEpisode = structuredDataObject(collect($graph)->firstWhere('@type', 'PodcastEpisode'));

    expect($podcastSeries)
        ->toMatchArray([
            '@id' => route('podcast.show', $podcast).'#podcast',
            'name' => $podcast->name,
            'url' => route('podcast.show', $podcast),
            'author' => [
                '@type' => 'Person',
                '@id' => route('about').'#person',
            ],
        ])
        ->and($podcastEpisode)
        ->toMatchArray([
            '@id' => route('podcast.episode', [$podcast, $episode]).'#episode',
            'name' => $episode->title,
            'url' => route('podcast.episode', [$podcast, $episode]),
            'episodeNumber' => 12,
            'duration' => 'PT1H2M5S',
            'datePublished' => Date::parse($episode->published_at)->toIso8601String(),
            'partOfSeries' => [
                '@type' => 'PodcastSeries',
                '@id' => route('podcast.show', $podcast).'#podcast',
            ],
        ])
        ->and($podcastEpisode)
        ->not->toHaveKey('associatedMedia');
});

it('renders canonical structured data for project case studies', function () {
    $project = Project::factory()->published()
        ->create([
            'url' => 'https://example.com/architecture-decisions',
            'github_url' => 'https://github.com/example/architecture-decisions',
            'tech_stack' => ['Laravel', 'Pest'],
        ]);

    $content = get(route('projects.show', $project))
        ->assertOk()
        ->getContent();

    $structuredData = decodeStructuredData($content);
    $graph = structuredDataGraph($structuredData['@graph'] ?? null);
    $projectCaseStudy = structuredDataObject(collect($graph)->firstWhere('@type', 'CreativeWork'));

    expect($projectCaseStudy)
        ->toMatchArray([
            '@id' => route('projects.show', $project).'#project',
            'name' => $project->title,
            'url' => route('projects.show', $project),
            'mainEntityOfPage' => route('projects.show', $project),
            'description' => $project->description,
            'author' => [
                '@type' => 'Person',
                '@id' => route('about').'#person',
            ],
            'keywords' => 'Laravel, Pest',
            'sameAs' => [
                $project->url,
            ],
        ])
        ->and(collect($graph)->pluck('@type'))
        ->not->toContain('SoftwareApplication');
});

it('renders canonical structured data for public content collections', function () {
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
        [route('blog.index'), 'Blog', $post->title, route('blog.show', $post)],
        [route('blog.category', $category), 'Architecture Articles', $post->title, route('blog.show', $post)],
        [route('blog.tag', $tag), 'Articles Tagged Boundaries', $post->title, route('blog.show', $post)],
        [route('projects.index'), 'Projects', $project->title, route('projects.show', $project)],
        [route('podcast.index'), 'Podcast', $podcast->name, route('podcast.show', $podcast)],
    ];

    foreach ($collections as [$url, $name, $itemName, $itemUrl]) {
        $content = get($url)
            ->assertOk()
            ->getContent();

        $structuredData = decodeStructuredData($content);
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
    }
});

it('uses page-specific metadata for paginated taxonomy archives', function () {
    $category = Category::factory()->create(['name' => 'Architecture']);
    $tag = Tag::factory()->create(['name' => 'Boundaries']);

    foreach (range(1, 11) as $index) {
        $post = Post::factory()->for($category)
            ->published()
            ->create(['published_at' => now()->subDays($index)]);
        $post->attachTag($tag);
    }

    foreach ([
        [
            'url' => route('blog.category', ['category' => $category, 'page' => 2]),
            'title' => 'Architecture Articles — Page 2 — Jeffrey Davidson',
            'description' => 'Articles about Architecture — Laravel development insights from Jeffrey Davidson. Page 2 of 2.',
        ],
        [
            'url' => route('blog.tag', ['tag' => $tag, 'page' => 2]),
            'title' => 'Articles Tagged Boundaries — Page 2 — Jeffrey Davidson',
            'description' => 'Articles tagged with Boundaries on The Laravel Architect. Page 2 of 2.',
        ],
    ] as $metadata) {
        $url = $metadata['url'];
        $content = get($url)
            ->assertOk()
            ->assertSeeHtml('<title>'.$metadata['title'].'</title>')
            ->assertSeeHtml('<meta name="description" content="'.$metadata['description'].'">')
            ->assertSeeHtml('<link rel="canonical" href="'.$url.'">')
            ->assertSeeHtml('<meta property="og:url" content="'.$url.'">')
            ->getContent();

        $structuredData = decodeStructuredData($content);
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
    }
});

it('returns not found for out-of-range taxonomy archive pages', function () {
    $category = Category::factory()->create();
    $tag = Tag::factory()->create();
    $post = Post::factory()->for($category)
        ->published()
        ->create();
    $post->attachTag($tag);

    foreach ([
        route('blog.category', ['category' => $category, 'page' => 2]),
        route('blog.tag', ['tag' => $tag, 'page' => 2]),
    ] as $url) {
        get($url)
            ->assertNotFound();
    }
});

it('uses page-specific metadata for paginated podcast archives', function () {
    $podcast = Podcast::factory()->create([
        'name' => 'Architecture Sessions',
        'description' => 'Conversations about maintainable Laravel applications.',
    ]);

    foreach (range(1, 21) as $index) {
        Episode::factory()->for($podcast)
            ->published()
            ->create([
                'title' => "Architecture Session {$index}",
                'slug' => "architecture-session-{$index}",
                'published_at' => now()->subDays($index),
            ]);
    }

    $url = route('podcast.show', ['podcast' => $podcast, 'page' => 2]);
    $content = get($url)
        ->assertOk()
        ->assertSeeHtml('<title>Architecture Sessions — Page 2 — Jeffrey Davidson</title>')
        ->assertSeeHtml('<meta name="description" content="Conversations about maintainable Laravel applications. Page 2 of 2.">')
        ->assertSeeHtml('<link rel="canonical" href="'.$url.'">')
        ->assertSeeHtml('<meta property="og:url" content="'.$url.'">')
        ->assertDontSee('Latest Episode')
        ->getContent();

    $structuredData = decodeStructuredData($content);
    $graph = structuredDataGraph($structuredData['@graph'] ?? null);
    $collectionPage = structuredDataObject(collect($graph)->firstWhere('@type', 'CollectionPage'));
    $itemList = structuredDataObject(collect($graph)->firstWhere('@type', 'ItemList'));
    $breadcrumbList = structuredDataObject(collect($graph)->firstWhere('@type', 'BreadcrumbList'));

    expect($collectionPage)
        ->toMatchArray([
            '@id' => $url.'#collection',
            'name' => 'Architecture Sessions Episodes',
            'url' => $url,
        ])
        ->and($itemList)
        ->toMatchArray([
            '@id' => $url.'#items',
            'numberOfItems' => 1,
        ])
        ->and(structuredDataListItem($itemList['itemListElement'] ?? null, 0))
        ->toMatchArray([
            '@type' => 'ListItem',
            'position' => 21,
            'name' => 'Architecture Session 21',
            'item' => route('podcast.episode', [$podcast, 'architecture-session-21']),
        ])
        ->and(structuredDataListItem($breadcrumbList['itemListElement'] ?? null, 2))
        ->toMatchArray([
            '@type' => 'ListItem',
            'position' => 3,
            'item' => $url,
        ]);
});

it('returns not found for out-of-range podcast archive pages', function () {
    $podcast = Podcast::factory()->create();
    Episode::factory()->for($podcast)
        ->published()
        ->create();

    get(route('podcast.show', ['podcast' => $podcast, 'page' => 2]))
        ->assertNotFound();
});

it('keeps one main landmark on public index pages', function (string $routeName) {
    $content = responseContent(get(route($routeName))
        ->assertOk()
        ->getContent());

    expect(substr_count($content, '<main'))->toBe(1)
        ->and(substr_count($content, '</main>'))
        ->toBe(1);
})->with([
    'projects' => 'projects.index',
    'podcasts' => 'podcast.index',
    'newsletter' => 'newsletter.index',
    'archive' => 'archive.index',
]);

it('renders only the site icon links', function () {
    $response = get(route('home'));

    $response->assertSeeHtml('<link rel="icon" type="image/png" sizes="32x32" href="/images/elephant-companion-32.png" />')
        ->assertDontSeeHtml('rel="shortcut icon"');
});

it('uses a concise primary navigation and a project-focused call to action', function () {
    $content = responseContent(get(route('home'))
        ->assertOk()
        ->assertSee('Writing')
        ->assertSee('Discuss a Project')
        ->assertSeeHtml('/images/elephant-companion-128.webp')
        ->assertDontSeeHtml('/images/logo-color.svg')
        ->getContent());

    expect($content)
        ->not->toContain('>Contact Me<')
        ->and(substr_count($content, '/images/elephant-companion-128.webp'))
        ->toBe(2);
});

it('provides a valid legacy favicon fallback', function () {
    $favicon = file_get_contents(public_path('favicon.ico'));

    $favicon = responseContent($favicon);

    expect(strlen($favicon))->toBeGreaterThan(0)
        ->and(substr($favicon, 0, 4))
        ->toBe("\x00\x00\x01\x00");
});

it('keeps public technology and channel details consistent', function () {
    get(route('about'))
        ->assertOk()
        ->assertSeeHtml('aria-label="Flip Jeffrey Davidson developer card"')
        ->assertSeeHtml('aria-pressed="false"')
        ->assertSeeHtml('x-data="siteHeader"')
        ->assertSee(configuredString(config('public-site.technology.laravel')))
        ->assertSeeHtml('>8.5</span>')
        ->assertSee('I share practical Laravel videos');

    get(route('uses'))
        ->assertOk()
        ->assertSee('Laravel '.configuredString(config('public-site.technology.laravel')))
        ->assertSee('Filament '.configuredString(config('public-site.technology.filament')));

    get(route('home'))
        ->assertOk()
        ->assertSeeHtml('https://youtube.com/@thelaravelarchitect')
        ->assertSee('Away from the editor');
});

it('keeps the developer card name out of the about page heading outline', function () {
    $content = responseContent(get(route('about'))
        ->assertOk()
        ->getContent());

    expect(stringPosition($content, '<h1'))
        ->toBeLessThan(stringPosition($content, '<h2'));
});

it('describes the developer card stat sheet to assistive technology', function () {
    get(route('about'))
        ->assertOk()
        ->assertSeeHtml('aria-describedby="about-card-stats"')
        ->assertSeeHtml('id="about-card-stats"');
});

it('places the mobile uses jump navigation before the equipment list', function () {
    $content = responseContent(get(route('uses'))
        ->assertOk()
        ->assertSeeHtml('aria-label="Jump to uses section"')
        ->getContent());

    expect(stringPosition($content, 'aria-label="Jump to uses section"'))
        ->toBeLessThan(stringPosition($content, 'id="hardware"'));
});

it('links the privacy notice from public collection points', function () {
    $privacyUrl = route('privacy');

    get(route('contact.create'))
        ->assertOk()
        ->assertSeeHtml($privacyUrl)
        ->assertSee('Your details are used to reply to this inquiry.');
});

it('loads public interactivity and typography from the local Vite bundle', function () {
    withVite();

    $manifest = assetManifest();

    get(route('home'))
        ->assertOk()
        ->assertDontSeeHtml('cdn.jsdelivr.net/npm/alpinejs')
        ->assertDontSeeHtml('fonts.bunny.net')
        ->assertDontSeeHtml($manifest['resources/css/filament/admin/theme.css']['file'])
        ->assertSeeHtml($manifest['resources/css/app.css']['file'])
        ->assertSeeHtml($manifest['resources/images/home-hero-desktop-1024.webp']['file'])
        ->assertSeeHtml($manifest['resources/images/home-hero-desktop-1536.webp']['file'])
        ->assertSeeHtml($manifest['resources/images/home-hero-mobile-640.webp']['file'])
        ->assertSeeHtml($manifest['resources/images/home-hero-mobile-1024.webp']['file'])
        ->assertSeeHtml($manifest['resources/images/podcast-coffee-logo-320.webp']['file'])
        ->assertSeeHtml($manifest['resources/images/podcast-coffee-logo-512.webp']['file'])
        ->assertSeeHtml($manifest['resources/js/app.js']['file']);

    get(route('about'))
        ->assertOk()
        ->assertSeeHtml($manifest['resources/css/app.css']['file'])
        ->assertSeeHtml($manifest['resources/images/avatar-320.webp']['file'])
        ->assertSeeHtml($manifest['resources/images/avatar-640.webp']['file'])
        ->assertSeeHtml('sizes="(min-width: 1024px) 300px, 250px"');

    get(route('blog.index'))
        ->assertOk()
        ->assertSeeHtml('x-data="siteHeader"')
        ->assertSeeHtml($manifest['resources/css/app.css']['file']);

    get(route('projects.index'))
        ->assertOk()
        ->assertSeeHtml('x-data="siteHeader"')
        ->assertSeeHtml($manifest['resources/css/app.css']['file']);

    get(route('podcast.index'))
        ->assertOk()
        ->assertSeeHtml($manifest['resources/css/app.css']['file']);

    expect($manifest)
        ->toHaveKey('resources/fonts/empera/Empera-Regular.woff2')
        ->not->toHaveKeys([
            'resources/css/pages/home-entry.css',
            'resources/css/pages/about-entry.css',
            'resources/css/pages/article-entry.css',
            'resources/css/pages/listings-entry.css',
            'resources/css/pages/podcast-entry.css',
        ])
        ->not->toHaveKey('resources/fonts/empera/Empera-Regular.ttf')
        ->and(implode("\n", array_column($manifest, 'file')))
        ->not->toContain('Empera-Vintage')
        ->not->toContain('Empera-Regular.ttf');
});

it('renders one concise client-focused services section', function () {
    $content = responseContent(get(route('home'))
        ->assertOk()
        ->assertSee('Where I can help')
        ->assertSee('Improve an existing codebase')
        ->assertSee('Build your application')
        ->assertSee('Ship with confidence')
        ->assertSee(route('services'))
        ->assertDontSeeHtml('data-architecture-scene')
        ->assertDontSee('How I can help')
        ->getContent());

    expect(substr_count($content, 'data-home-services'))->toBe(1);
});

it('prioritizes the art-directed homepage hero', function () {
    get(route('home'))
        ->assertOk()
        ->assertSeeHtml('media="(max-width: 767px)"')
        ->assertSeeHtml('sizes="100vw"')
        ->assertSeeHtml('fetchpriority="high"')
        ->assertSeeHtml('decoding="async"')
        ->assertSeeHtml('Laravel systems,')
        ->assertSeeHtml('easier to change.');
});

it('gives every homepage article a responsive image', function () {
    withVite();

    $posts = [
        'what-15-years-of-web-development-taught-me',
        'why-i-still-choose-laravel-in-2026',
        'how-i-structure-every-laravel-project',
    ];

    foreach ($posts as $index => $slug) {
        Post::factory()->published()
            ->create([
                'slug' => $slug,
                'published_at' => now()->subDays($index),
            ]);
    }

    get(route('home'))
        ->assertOk()
        ->assertSeeHtml('home-writing-fallback-384')
        ->assertSeeHtml('home-writing-fallback-768')
        ->assertSeeHtml('home-writing-review-384')
        ->assertSeeHtml('home-writing-review-768')
        ->assertSeeHtml('home-writing-modules-384')
        ->assertSeeHtml('home-writing-modules-768');
});

it('places the theme bootstrap inside the document head', function () {
    $content = responseContent(get(route('home'))
        ->assertOk()
        ->getContent());

    expect(stringPosition($content, '<head>'))
        ->toBeLessThan(stringPosition($content, 'Sync theme before paint'));
});

it('renders accessible podcast episode embeds and external links', function () {
    $podcast = Podcast::factory()->create(['color' => '#2563eb']);
    $episode = Episode::factory()->for($podcast)
        ->published()
        ->create([
            'title' => 'Designing Laravel Applications',
            'youtube_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
        ]);

    get(route('podcast.episode', [$podcast, $episode]))
        ->assertOk()
        ->assertSeeHtml('Play Designing Laravel Applications on YouTube')
        ->assertSeeHtml('title="Designing Laravel Applications on YouTube"')
        ->assertSeeHtml('www.youtube-nocookie.com/embed/dQw4w9WgXcQ')
        ->assertSeeHtml('data-youtube-facade')
        ->assertSeeHtml('data-youtube-player')
        ->assertSeeHtml('data-youtube-play')
        ->assertSeeHtml('x-data="siteHeader"')
        ->assertDontSeeHtml('src="https://www.youtube.com/embed/')
        ->assertSeeHtml('rel="noopener noreferrer"')
        ->assertSeeHtml('data-podcast-copy-url=')
        ->assertSeeHtml('style="--podcast-color: #2563eb;"')
        ->assertDontSeeHtml('<style>')
        ->assertDontSeeHtml('onclick=');

    get(route('podcast.show', $podcast))
        ->assertOk()
        ->assertSeeHtml('style="--podcast-color: #2563eb;"')
        ->assertSeeHtml('[--dur:0.7s]')
        ->assertDontSeeHtml('style="--dur:')
        ->assertDontSeeHtml('<style>');
});

it('falls back to a safe podcast color when stored presentation data is invalid', function () {
    $podcast = Podcast::factory()->create(['color' => 'url(https://example.com/image.png)']);

    get(route('podcast.show', $podcast))
        ->assertOk()
        ->assertSeeHtml('style="--podcast-color: #6366f1;"')
        ->assertDontSeeHtml('url(https://example.com/image.png)');
});

it('keeps the admin panel behind authentication', function () {
    withVite();

    $manifest = assetManifest();

    get('/admin')
        ->assertRedirect('/admin/login');
    get('/admin/login')
        ->assertOk()
        ->assertSeeHtml($manifest['resources/css/filament/admin/theme.css']['file'])
        ->assertSee('Appearance')
        ->assertSee('Enable light theme')
        ->assertSee('Enable dark theme')
        ->assertSee('Enable system theme');
});

it('uses published work as homepage proof', function () {
    Post::factory()->published()
        ->create();

    Post::factory()->create();

    Project::factory()->published()
        ->create();

    Project::factory()->create();

    get(route('home'))
        ->assertOk()
        ->assertViewHas('publishedPostCount', 1)
        ->assertViewHas('publishedProjectCount', 1)
        ->assertDontSee('Approved Client')
        ->assertDontSee('Recommendations')
        ->assertDontSee('Pending Client')
        ->assertSeeInOrder([
            'Years building PHP',
            'Published articles',
            'Portfolio projects',
        ]);
});

it('does not present zero-value homepage proof', function () {
    get(route('home'))
        ->assertOk()
        ->assertSee('Years building PHP')
        ->assertDontSee('Published articles')
        ->assertDontSee('Portfolio projects')
        ->assertDontSee('Recommendations');
});

it('presents published projects as case studies without inferring product status', function () {
    $project = Project::factory()->published()
        ->featured()
        ->create([
            'content' => '## The challenge\n\nModel a complex domain without hiding its rules.',
            'github_url' => 'https://github.com/example/architecture-decisions',
            'tech_stack' => ['Laravel', 'Pest'],
        ]);

    get(route('projects.index'))
        ->assertOk()
        ->assertSee('Selected projects')
        ->assertSee('Explore the project')
        ->assertSee($project->title);

    get(route('projects.show', $project))
        ->assertOk()
        ->assertSee('Project overview')
        ->assertSee('Discuss a similar project')
        ->assertSee('The challenge')
        ->assertDontSee('Active');
});

it('serves responsive project images while retaining the original fallback', function () {
    Storage::fake('public');
    $image = UploadedFile::fake()->image('architecture.png', 1280, 72);
    Storage::disk('public')->put('projects/architecture.png', $image->getContent());

    $project = Project::factory()->published()
        ->featured()
        ->create(['featured_image_path' => 'projects/architecture.png']);

    get(route('home'))
        ->assertOk()
        ->assertSeeHtml('type="image/webp"')
        ->assertSeeHtml('architecture-640.webp')
        ->assertSeeHtml('architecture-1280.webp')
        ->assertSeeHtml(configuredString($project->featured_image_url));

    get(route('projects.show', $project))
        ->assertOk()
        ->assertSeeHtml('type="image/webp"')
        ->assertSeeHtml('aspect-video')
        ->assertSeeHtml('fetchpriority="high"')
        ->assertSeeHtml(configuredString($project->featured_image_url));
});

it('serves responsive post images while retaining the original fallback', function () {
    Storage::fake('public');
    $image = UploadedFile::fake()->image('article.png', 1280, 72);
    Storage::disk('public')->put('posts/article.png', $image->getContent());

    $post = Post::factory()->published()
        ->create(['featured_image_path' => 'posts/article.png']);

    get(route('blog.show', $post))
        ->assertOk()
        ->assertSeeHtml('type="image/webp"')
        ->assertSeeHtml('article-640.webp')
        ->assertSeeHtml('article-1280.webp')
        ->assertSeeHtml('sizes="(min-width: 1280px) 1216px, calc(100vw - 2rem)"')
        ->assertSeeHtml('aspect-[3/2]')
        ->assertSeeHtml('fetchpriority="high"')
        ->assertSeeHtml(configuredString($post->featured_image_url));
});

it('serves responsive podcast cover images while retaining the original fallback', function () {
    Storage::fake('public');
    $image = UploadedFile::fake()->image('podcast.png', 1280, 72);
    Storage::disk('public')->put('podcasts/podcast.png', $image->getContent());

    $podcast = Podcast::factory()->create(['cover_image_path' => 'podcasts/podcast.png']);

    get(route('podcast.index'))
        ->assertOk()
        ->assertSeeHtml('type="image/webp"')
        ->assertSeeHtml('podcast-640.webp')
        ->assertSeeHtml('podcast-1280.webp')
        ->assertSeeHtml('sizes="288px"')
        ->assertSeeHtml('fetchpriority="high"')
        ->assertSeeHtml(configuredString($podcast->cover_image_url));

    get(route('podcast.show', $podcast))
        ->assertOk()
        ->assertSeeHtml('sizes="224px"')
        ->assertSeeHtml(configuredString($podcast->cover_image_url));
});

it('shares the podcast cover in social cards', function (?string $coverImagePath, string $slug) {
    withVite();
    Storage::fake('public', ['url' => config('filesystems.disks.public.url')]);
    $podcast = Podcast::factory()->create([
        'slug' => $slug,
        'cover_image_path' => $coverImagePath,
    ]);
    $coverImageUrl = configuredString($podcast->cover_image_url);

    get(route('podcast.show', $podcast))
        ->assertOk()
        ->assertSeeHtml("<meta property=\"og:image\" content=\"{$coverImageUrl}\">")
        ->assertSeeHtml('<meta name="twitter:card" content="summary_large_image">')
        ->assertDontSeeHtml('logo-color-black-bg.png');
})->with([
    'uploaded cover' => ['podcasts/cover.png', 'shared-podcast'],
    'bundled cover' => [null, 'coffee-with-the-laravel-architect'],
]);

it('shares the site image for a podcast without a cover', function () {
    $podcast = Podcast::factory()->create();

    get(route('podcast.show', $podcast))
        ->assertOk()
        ->assertSeeHtml('<meta property="og:image" content="'.secure_url('/images/logo-color-black-bg.png').'">')
        ->assertSeeHtml('<meta name="twitter:card" content="summary">');
});

it('serves responsive optimized fallback artwork for known podcasts', function () {
    withVite();

    $podcast = Podcast::factory()->create(['slug' => 'coffee-with-the-laravel-architect']);

    get(route('podcast.show', $podcast))
        ->assertOk()
        ->assertSeeHtml('srcset="'.$podcast->fallback_cover_image_srcset.'"')
        ->assertSeeHtml('sizes="224px"')
        ->assertSeeHtml(configuredString($podcast->cover_image_url));
});

it('shows synced published YouTube videos without stale launch content', function () {
    $publishedVideo = Video::factory()->create();
    $futureVideo = Video::factory()->create(['published_at' => now()->addDay()]);

    get(route('home'))
        ->assertOk()
        ->assertSee($publishedVideo->title)
        ->assertSee($publishedVideo->youtube_url)
        ->assertDontSee($futureVideo->title)
        ->assertDontSee('Launching March 2')
        ->assertDontSee('Coming to the Channel')
        ->assertDontSee('before launch day');
});

it('hides inactive podcasts from public podcast surfaces', function () {
    $activePodcast = Podcast::factory()->create();

    $inactivePodcast = Podcast::factory()->inactive()
        ->create();

    $episode = Episode::factory()->for($inactivePodcast)
        ->published()
        ->create();

    get('/podcasts')
        ->assertOk()
        ->assertSee($activePodcast->name)
        ->assertDontSee($inactivePodcast->name)
        ->assertDontSee('Real Talk on Hard Days');

    get('/')
        ->assertOk()
        ->assertDontSee($inactivePodcast->name)
        ->assertDontSeeHtml('toHaveCount</span>(<span class="syn-variable">2</span>');

    get(route('podcast.show', $inactivePodcast))
        ->assertNotFound();
    get(route('podcast.episode', [$inactivePodcast, $episode]))
        ->assertNotFound();
});
