<?php

use App\Models\Category;
use App\Models\Post;
use App\Presenters\PostPresenter;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Vite;

use function Pest\Laravel\get;
use function Pest\Laravel\travelTo;
use function Pest\Laravel\withVite;

pest()->use(RefreshDatabase::class);

require_once __DIR__.'/../../../Support/PublicPageAssertions.php';

describe('index', function () {
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

        $response = get($url);

        $response
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

        $response = get(route('blog.index', ['page' => 2]));

        $response
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
        $response = get($url);

        $response
            ->assertOk()
            ->assertSeeHtml('<title>Blog — Page 2 — Jeffrey Davidson</title>')
            ->assertSeeHtml('<link rel="canonical" href="'.$url.'">')
            ->assertSeeHtml('Stable Article 1');

        $content = $response->getContent();

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

    it('rejects invalid public blog filters', function (array $filters) {
        $response = get(route('blog.index', $filters));

        $response->assertNotFound();
    })->with([
        'unknown category' => [['category' => 'missing-category']],
        'oversized query' => [['q' => str_repeat('x', 121)]],
    ]);

    it('preserves a zero-valued search filter in canonical category links', function () {
        Post::factory()->published()
            ->create();

        $response = get(route('blog.index', ['q' => '0']));

        $response
            ->assertOk()
            ->assertSeeHtml('href="'.route('blog.index', ['q' => '0']).'"')
            ->assertSeeHtml('value="0"');
    });

    it('lists published posts and hides drafts and scheduled posts', function () {
        $published = Post::factory()->published()
            ->create(['title' => 'Published Laravel Architecture']);
        $draft = Post::factory()->create(['title' => 'Draft Laravel Architecture']);
        $scheduled = Post::factory()->published()
            ->create(['title' => 'Scheduled Laravel Architecture', 'published_at' => now()->addDay()]);

        $response = get(route('blog.index'));

        $response
            ->assertOk()
            ->assertSee($published->title)
            ->assertDontSee($draft->title)
            ->assertDontSee($scheduled->title);
    });

    it('counts only published posts in blog categories', function () {
        $published = Post::factory()->published()
            ->create();
        Post::factory()->create([
            'category_id' => $published->category_id,
        ]);

        $response = get(route('blog.index'));

        $response
            ->assertOk()
            ->assertViewHas('categories', function (mixed $categories): bool {
                if (! $categories instanceof Collection) {
                    return false;
                }

                $category = $categories->sole();

                return $category instanceof Category && $category->getAttribute('posts_count') === 1;
            });
    });

    it('shows a post publish date in the display timezone while its metadata keeps the UTC instant on blog.index', function () {
        config(['app.display_timezone' => 'America/New_York']);
        travelTo('2026-10-10 12:00:00');
        $post = Post::factory()->published()
            ->create(['published_at' => '2026-10-06 01:00:00']);
        $response = get(route('blog.index'));
        $response->assertOk()
            ->assertSeeHtml('datetime="2026-10-05"')
            ->assertSee('Oct 05, 2026')
            ->assertDontSee('Oct 06, 2026');
    });

    it('loads public interactivity and typography from the local Vite bundle on blog.index', function () {
        withVite();
        $manifest = assetManifest();
        $response = get(route('blog.index'));
        $response->assertOk()
            ->assertSeeHtml('x-data="siteHeader"')
            ->assertSeeHtml($manifest['resources/css/app.css']['file']);
    });
});

describe('show', function () {
    it('renders a published post', function () {
        // Arrange
        $published = Post::factory()
            ->published()
            ->create([
                'title' => 'Shipping a Laravel feature',
                'content' => 'The body of the published post.',
            ]);

        // Act
        $response = get(route('blog.show', $published));

        // Assert
        $response->assertOk()
            ->assertSee('Shipping a Laravel feature')
            ->assertSee('The body of the published post.');
    });

    it('does not expose a draft post', function () {
        $published = Post::factory()->published()
            ->create();
        $draft = Post::factory()->create();
        $scheduled = Post::factory()->published()
            ->create(['published_at' => now()->addDay()]);
        $response = get(route('blog.show', $draft));
        $response->assertNotFound();
    });

    it('does not expose a future post', function () {
        $published = Post::factory()->published()
            ->create();
        $draft = Post::factory()->create();
        $scheduled = Post::factory()->published()
            ->create(['published_at' => now()->addDay()]);
        $response = get(route('blog.show', $scheduled));
        $response->assertNotFound();
    });

    it('renders post content as markdown with anchored headings', function () {
        $post = Post::factory()->published()
            ->create([
                'content' => <<<'MARKDOWN'
        ## Native Markdown

        This is **rendered** content.

        <script>alert('unsafe')</script>

        [Unsafe link](javascript:alert('unsafe'))
        MARKDOWN,
            ]);

        $response = get(route('blog.show', $post));

        $response
            ->assertOk()
            ->assertSeeHtml('<h2 id="native-markdown">Native Markdown</h2>')
            ->assertSeeHtml('This is <strong>rendered</strong> content.')
            ->assertDontSeeHtml("<script>alert('unsafe')</script>")
            ->assertDontSeeHtml('javascript:');
    });

    it('renders model-specific SEO metadata', function () {
        $post = Post::factory()->published()
            ->create([
                'title' => 'Designing Clear Laravel Boundaries',
                'excerpt' => 'A focused guide to keeping Laravel applications maintainable.',
            ]);

        $response = get(route('blog.show', $post));

        $response
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

        $response = get(route('blog.show', $post));

        $content = responseContent($response->getContent());

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
            fn (Post $post): ?string => PostPresenter::from($post)->featuredImageUrl(),
        ],
        'bundled artwork' => [
            'hello-world-why-im-starting-this-blog',
            null,
            fn (): string => Vite::asset('resources/images/post-hello-world-1280.webp'),
        ],
        'no artwork' => [
            'article-without-artwork',
            null,
            fn (Post $post): string => route('ogImage', $post),
        ],
    ]);

    it('renders canonical structured data for the site and blog posts', function () {
        $post = Post::factory()->published()
            ->create();

        $response = get(route('blog.show', $post));

        $response
            ->assertOk();

        $content = $response->getContent();

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

        $response = get(route('blog.show', $post));

        $response
            ->assertDontSeeHtml('</script><h1>');

        $content = $response->getContent();

        $graph = structuredDataGraph(decodeStructuredData($content)['@graph'] ?? null);
        $article = structuredDataObject(collect($graph)->firstWhere('@type', 'Article'));

        expect($article['headline'] ?? null)
            ->toBe($title);
    });

    it('serves responsive post images while retaining the original fallback', function () {
        Storage::fake('public');
        $image = UploadedFile::fake()->image('article.png', 1280, 72);
        Storage::disk('public')->put('posts/article.png', $image->getContent());

        $post = Post::factory()->published()
            ->create(['featured_image_path' => 'posts/article.png']);

        $response = get(route('blog.show', $post));

        $response
            ->assertOk()
            ->assertSeeHtml('type="image/webp"')
            ->assertSeeHtml('article-640.webp')
            ->assertSeeHtml('article-1280.webp')
            ->assertSeeHtml('sizes="(min-width: 1280px) 1216px, calc(100vw - 2rem)"')
            ->assertSeeHtml('aspect-[3/2]')
            ->assertSeeHtml('fetchpriority="high"')
            ->assertSeeHtml(configuredString(PostPresenter::from($post)->featuredImageUrl()));
    });

    it('shows a post publish date in the display timezone while its metadata keeps the UTC instant on blog.show', function () {
        config(['app.display_timezone' => 'America/New_York']);
        travelTo('2026-10-10 12:00:00');
        $post = Post::factory()->published()
            ->create(['published_at' => '2026-10-06 01:00:00']);
        $response = get(route('blog.show', $post));
        $response->assertOk()
            ->assertSeeHtml('<time datetime="2026-10-05">October 05, 2026</time>')
            ->assertSeeHtml('<meta property="article:published_time" content="2026-10-06T01:00:00+00:00">')
            ->assertSeeHtml('"datePublished":"2026-10-06T01:00:00+00:00"');
    });
});

describe('shared contracts', function () {
    it('renders bundled editorial artwork and article navigation for a seeded post', function (string $slug, string $asset) {
        // Arrange
        withVite();

        $post = Post::factory()->published()
            ->create([
                'slug' => $slug,
                'content' => "## A Starting Point\n\nStart with Laravel.\n\n## Thin Controllers\n\nKeep the boundary clear.",
            ]);

        // Act
        $response = get(route('blog.show', $post));

        // Assert
        $response
            ->assertOk()
            ->assertSeeHtml("data-post-artwork=\"{$slug}\"")
            ->assertSeeHtml($asset)
            ->assertSeeHtml(str_replace('-768', '-384', $asset))
            ->assertSeeHtml('data-article-toc');
    })->with('seeded post artwork');

    it('renders bundled editorial artwork for every seeded post on the blog index', function () {
        // Arrange
        withVite();

        $slugs = [
            'hello-world-why-im-starting-this-blog',
            'from-kansas-to-florida-a-developers-journey',
            'what-15-years-of-web-development-taught-me',
            'why-i-still-choose-laravel-in-2026',
            'how-i-structure-every-laravel-project',
        ];

        foreach ($slugs as $slug) {
            Post::factory()->published()
                ->create([
                    'slug' => $slug,
                    'content' => "## A Starting Point\n\nStart with Laravel.\n\n## Thin Controllers\n\nKeep the boundary clear.",
                ]);
        }

        // Act
        $response = get(route('blog.index'));

        // Assert
        $response->assertOk();

        foreach ($slugs as $slug) {
            $response->assertSeeHtml("data-post-artwork=\"{$slug}\"");
        }
    });
});

dataset('seeded post artwork', [
    'hello-world-why-im-starting-this-blog' => ['hello-world-why-im-starting-this-blog', 'post-hello-world-768'],
    'from-kansas-to-florida-a-developers-journey' => ['from-kansas-to-florida-a-developers-journey', 'post-kansas-florida-768'],
    'what-15-years-of-web-development-taught-me' => ['what-15-years-of-web-development-taught-me', 'home-writing-modules-768'],
    'why-i-still-choose-laravel-in-2026' => ['why-i-still-choose-laravel-in-2026', 'home-writing-review-768'],
    'how-i-structure-every-laravel-project' => ['how-i-structure-every-laravel-project', 'home-writing-fallback-768'],
]);
