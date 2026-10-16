<?php

use App\Models\Episode;
use App\Models\Podcast;
use App\Presenters\PodcastPresenter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\get;
use function Pest\Laravel\withVite;

pest()->use(RefreshDatabase::class);

require_once __DIR__.'/../../../Support/PublicPageAssertions.php';

describe('index', function () {
    it('serves responsive podcast cover images while retaining the original fallback on podcasts.index', function () {
        Storage::fake('public');
        $image = UploadedFile::fake()->image('podcast.png', 1280, 72);
        Storage::disk('public')->put('podcasts/podcast.png', $image->getContent());
        $podcast = Podcast::factory()->create(['cover_image_path' => 'podcasts/podcast.png']);
        $response = get(route('podcasts.index'));
        $response->assertOk()
            ->assertSeeHtml('type="image/webp"')
            ->assertSeeHtml('podcast-640.webp')
            ->assertSeeHtml('podcast-1280.webp')
            ->assertSeeHtml('sizes="288px"')
            ->assertSeeHtml('fetchpriority="high"')
            ->assertSeeHtml(configuredString(PodcastPresenter::from($podcast)->coverImageUrl()));
    });

    it('loads public interactivity and typography from the local Vite bundle on podcasts.index', function () {
        withVite();
        $manifest = assetManifest();
        $response = get(route('podcasts.index'));
        $response->assertOk()
            ->assertSeeHtml($manifest['resources/css/app.css']['file']);
        expect($manifest)->not->toHaveKey('resources/fonts/empera/Empera-Regular.woff2')
            ->not->toHaveKeys(['resources/css/pages/home-entry.css', 'resources/css/pages/about-entry.css', 'resources/css/pages/article-entry.css', 'resources/css/pages/listings-entry.css', 'resources/css/pages/podcast-entry.css'])
            ->not->toHaveKey('resources/fonts/empera/Empera-Regular.ttf')
            ->and(implode("\n", array_column($manifest, 'file')))
            ->not->toContain('Empera')
            ->and(file_get_contents(public_path('build/'.$manifest['resources/css/app.css']['file'])))
            ->not->toContain('Empera', 'empera')
            ->and(file_exists(resource_path('fonts/empera/Empera-Regular.ttf')))
            ->toBeFalse()
            ->and(file_exists(resource_path('fonts/empera/Empera-Regular.woff2')))
            ->toBeFalse();
    });
});

describe('show', function () {
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

        $url = route('podcasts.show', ['podcast' => $podcast, 'page' => 2]);
        $response = get($url);

        $response
            ->assertOk()
            ->assertSeeHtml('<title>Architecture Sessions — Page 2 — Jeffrey Davidson</title>')
            ->assertSeeHtml('<meta name="description" content="Conversations about maintainable Laravel applications. Page 2 of 2.">')
            ->assertSeeHtml('<link rel="canonical" href="'.$url.'">')
            ->assertSeeHtml('<meta property="og:url" content="'.$url.'">')
            ->assertDontSee('Latest Episode');

        $content = $response->getContent();

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
                'item' => route('podcasts.episode', [$podcast, 'architecture-session-21']),
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

        $response = get(route('podcasts.show', ['podcast' => $podcast, 'page' => 2]));

        $response
            ->assertNotFound();
    });

    it('falls back to a safe podcast color when stored presentation data is invalid', function () {
        $podcast = Podcast::factory()->create(['color' => 'url(https://example.com/image.png)']);

        $response = get(route('podcasts.show', $podcast));

        $response
            ->assertOk()
            ->assertSeeHtml('style="--podcast-color: #6366f1;"')
            ->assertDontSeeHtml('url(https://example.com/image.png)');
    });

    it('shares the podcast cover in social cards', function (?string $coverImagePath, string $slug) {
        withVite();
        Storage::fake('public', ['url' => config('filesystems.disks.public.url')]);
        $podcast = Podcast::factory()->create([
            'slug' => $slug,
            'cover_image_path' => $coverImagePath,
        ]);
        $coverImageUrl = configuredString(PodcastPresenter::from($podcast)->coverImageUrl());

        $response = get(route('podcasts.show', $podcast));

        $response
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

        $response = get(route('podcasts.show', $podcast));

        $response
            ->assertOk()
            ->assertSeeHtml('<meta property="og:image" content="'.secure_url('/images/logo-color-black-bg.png').'">')
            ->assertSeeHtml('<meta name="twitter:card" content="summary">');
    });

    it('serves responsive optimized fallback artwork for known podcasts', function () {
        withVite();

        $podcast = Podcast::factory()->create(['slug' => 'coffee-with-the-laravel-architect']);
        $cover = PodcastPresenter::from($podcast)->cover();

        $response = get(route('podcasts.show', $podcast));

        $response
            ->assertOk()
            ->assertSeeHtml('srcset="'.$cover?->srcset.'"')
            ->assertSeeHtml('sizes="224px"')
            ->assertSeeHtml(configuredString(PodcastPresenter::from($podcast)->coverImageUrl()));
    });

    it('hides inactive podcasts from public podcast surfaces on /podcasts', function () {
        $activePodcast = Podcast::factory()->create();
        $inactivePodcast = Podcast::factory()->inactive()
            ->create();
        $episode = Episode::factory()->for($inactivePodcast)
            ->published()
            ->create();
        $response = get(route('podcasts.index'));
        $response->assertOk()
            ->assertSee($activePodcast->name)
            ->assertDontSee($inactivePodcast->name)
            ->assertDontSee('Real Talk on Hard Days');
    });

    it('hides inactive podcasts from public podcast surfaces on podcasts.show', function () {
        $activePodcast = Podcast::factory()->create();
        $inactivePodcast = Podcast::factory()->inactive()
            ->create();
        $episode = Episode::factory()->for($inactivePodcast)
            ->published()
            ->create();
        $response = get(route('podcasts.show', $inactivePodcast));
        $response->assertNotFound();
    });

    it('serves responsive podcast cover images while retaining the original fallback on podcasts.show', function () {
        Storage::fake('public');
        $image = UploadedFile::fake()->image('podcast.png', 1280, 72);
        Storage::disk('public')->put('podcasts/podcast.png', $image->getContent());
        $podcast = Podcast::factory()->create(['cover_image_path' => 'podcasts/podcast.png']);
        $response = get(route('podcasts.show', $podcast));
        $response->assertOk()
            ->assertSeeHtml('sizes="224px"')
            ->assertSeeHtml(configuredString(PodcastPresenter::from($podcast)->coverImageUrl()));
    });

    it('styles its episodes with the podcast colour and no inline style blocks on podcasts.show', function () {
        $podcast = Podcast::factory()->create(['color' => '#2563eb']);
        $episode = Episode::factory()->for($podcast)
            ->published()
            ->create(['title' => 'Designing Laravel Applications', 'youtube_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ']);
        $response = get(route('podcasts.show', $podcast));
        $response->assertOk()
            ->assertSeeHtml('style="--podcast-color: #2563eb;"')
            ->assertSeeHtml('[--dur:0.7s]')
            ->assertDontSeeHtml('style="--dur:')
            ->assertDontSeeHtml('<style>');
    });
});
