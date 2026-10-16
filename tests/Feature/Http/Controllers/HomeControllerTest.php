<?php

use App\Enums\SocialPlatform;
use App\Models\Podcast;
use App\Models\Post;
use App\Models\Project;
use App\Models\SocialProfile;
use App\Models\Video;
use App\Presenters\VideoPresenter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\Support\SocialProfileFixtures;

use function Pest\Laravel\get;
use function Pest\Laravel\withVite;

pest()->use(RefreshDatabase::class);

require_once __DIR__.'/../../../Support/PublicPageAssertions.php';

use App\Presenters\ProjectPresenter;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

it('renders the homepage without requesting external statistics on a cold cache', function () {
    Cache::flush();
    Http::fake();

    $response = get(route('home'));

    $response
        ->assertOk();

    Http::assertNothingSent();
});

it('presents featured projects without duplicated summaries or invented artwork', function (int $count) {
    foreach (range(1, $count) as $index) {
        Project::factory()->published()
            ->featured()
            ->create([
                'description' => "A distinct project summary {$index}.",
            ]);
    }

    $response = get(route('home'));

    $response->assertOk()
        ->assertSee('Selected work')
        ->assertDontSee('Domain overview')
        ->assertDontSee('home-case-study-fallback');
    $content = $response->getContent();
    if (! is_string($content)) {
        throw new RuntimeException('Expected homepage HTML.');
    }

    expect(substr_count($content, 'data-project-entry'))->toBe($count)
        ->and(substr_count($content, 'A distinct project summary 1.'))
        ->toBe(1);
})->with([1, 2, 4]);

it('omits selected work when no projects are featured', function () {
    $response = get(route('home'));

    $response
        ->assertOk()
        ->assertDontSeeHtml('data-home-work');
});

it('uses the active podcast artwork on the homepage', function () {
    Podcast::factory()->create([
        'cover_image_path' => 'podcasts/current-cover.webp',
    ]);

    $response = get(route('home'));

    $response
        ->assertOk()
        ->assertSeeHtml('/storage/podcasts/current-cover.webp');
});

it('presents honest inquiry links and one media destination per channel', function () {
    $response = get(route('home'));

    $response
        ->assertOk()
        ->assertSee('Explore services')
        ->assertDontSee('Book a review')
        ->assertSee('Listen to the podcast')
        ->assertDontSee('Browse the podcast')
        ->assertDontSee('Field notes');
});

it('renders enabled footer social profiles and hides contact-only and disabled ones', function () {
    SocialProfile::query()->delete();

    $footerProfile = SocialProfileFixtures::create(SocialPlatform::GitHub, 'https://github.com/footer-only', true, false);
    $contactProfile = SocialProfileFixtures::create(SocialPlatform::LinkedIn, 'https://linkedin.com/in/contact-only', false, true);
    SocialProfileFixtures::create(SocialPlatform::Bluesky, 'https://bsky.app/profile/disabled', true, true, false);

    $response = get(route('home'));

    $response
        ->assertSeeHtml($footerProfile->url)
        ->assertDontSeeHtml($contactProfile->url)
        ->assertDontSeeHtml('https://bsky.app/profile/disabled');
});

it('uses the enabled YouTube profile for the homepage channel link', function () {
    SocialProfile::query()->delete();

    $youtubeProfile = SocialProfileFixtures::create(
        SocialPlatform::YouTube,
        'https://youtube.com/@managed-channel',
        false,
        false,
    );

    $response = get(route('home'));

    $response
        ->assertSeeHtml($youtubeProfile->url);

    $youtubeProfile->update(['is_enabled' => false]);

    $response = get(route('home'));

    $response
        ->assertDontSeeHtml($youtubeProfile->url);
});

it('prioritizes main destinations and keeps secondary links discoverable', function (): void {
    $response = get(route('home'));
    $response->assertOk();

    $content = $response->getContent();

    if (! is_string($content)) {
        throw new RuntimeException('The home page response did not contain HTML.');
    }

    $mainPosition = strpos($content, '<main');

    if (! is_int($mainPosition)) {
        throw new RuntimeException('The home page response did not contain a main landmark.');
    }

    $header = substr($content, 0, $mainPosition);

    foreach (['Services', 'Projects', 'Writing', 'About', 'Search', 'Archive'] as $label) {
        expect(str_contains($header, ">{$label}<"))->toBeTrue();
    }

    foreach (['Podcast', 'Uses'] as $label) {
        expect(str_contains($header, ">{$label}<"))
            ->toBeFalse()
            ->and(str_contains($content, ">{$label}<"))
            ->toBeTrue();
    }

    $aboutPosition = strpos($header, '>About<');
    $searchPosition = strpos($header, '>Search<');
    $archivePosition = strpos($header, '>Archive<');

    if (! is_int($aboutPosition) || ! is_int($searchPosition) || ! is_int($archivePosition)) {
        throw new RuntimeException('The home page header did not contain its primary and utility links.');
    }

    expect($aboutPosition)
        ->toBeLessThan($searchPosition)
        ->and($searchPosition)
        ->toBeLessThan($archivePosition)
        ->and(str_contains($content, 'Discuss a Project'))
        ->toBeTrue();
});

it('renders one concise client-focused services section', function () {
    $response = get(route('home'));

    $response
        ->assertOk()
        ->assertSee('Where I can help')
        ->assertSee('Improve an existing codebase')
        ->assertSee('Build your application')
        ->assertSee('Ship with confidence')
        ->assertSee(route('services'))
        ->assertDontSeeHtml('data-architecture-scene')
        ->assertDontSee('How I can help');

    $content = responseContent($response->getContent());

    expect(substr_count($content, 'data-home-services'))->toBe(1);
});

it('prioritizes the art-directed homepage hero', function () {
    $response = get(route('home'));

    $response
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

    $response = get(route('home'));

    $response
        ->assertOk()
        ->assertSeeHtml('home-writing-fallback-384')
        ->assertSeeHtml('home-writing-fallback-768')
        ->assertSeeHtml('home-writing-review-384')
        ->assertSeeHtml('home-writing-review-768')
        ->assertSeeHtml('home-writing-modules-384')
        ->assertSeeHtml('home-writing-modules-768');
});

it('uses published work as homepage proof', function () {
    Post::factory()->published()
        ->create();

    Post::factory()->create();

    Project::factory()->published()
        ->create();

    Project::factory()->create();

    $response = get(route('home'));

    $response
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
    $response = get(route('home'));

    $response
        ->assertOk()
        ->assertSee('Years building PHP')
        ->assertDontSee('Published articles')
        ->assertDontSee('Portfolio projects')
        ->assertDontSee('Recommendations');
});

it('shows synced published YouTube videos without stale launch content', function () {
    $publishedVideo = Video::factory()->create();
    $futureVideo = Video::factory()->create(['published_at' => now()->addDay()]);

    $response = get(route('home'));

    $response
        ->assertOk()
        ->assertSee($publishedVideo->title)
        ->assertSee(VideoPresenter::from($publishedVideo)->youtubeUrl())
        ->assertDontSee($futureVideo->title)
        ->assertDontSee('Launching March 2')
        ->assertDontSee('Coming to the Channel')
        ->assertDontSee('before launch day');
});

it('shares pages with a territory-specific Open Graph locale', function () {
    $response = get(route('home'));

    $response
        ->assertOk()
        ->assertSeeHtml('<meta property="og:locale" content="en_US">');
});

it('names the mobile theme toggle by its visible label', function () {
    $response = get(route('home'));

    $response
        ->assertOk();

    $content = responseContent($response->getContent());

    preg_match('/<button[^>]*theme-toggle-mobile[^>]*>/', $content, $mobileToggle);

    expect($mobileToggle[0] ?? '')
        ->toContain('data-theme-toggle')
        ->not->toContain('aria-label')
        ->and(substr_count($content, 'aria-label="Toggle theme"'))
        ->toBe(1);
});

it('renders only the site icon links', function () {
    $response = get(route('home'));

    $response->assertSeeHtml('<link rel="icon" type="image/png" sizes="32x32" href="/images/elephant-small-32.png" />')
        ->assertSeeHtml('<link rel="icon" type="image/png" sizes="16x16" href="/images/elephant-small-16.png" />')
        ->assertSeeHtml('<link rel="apple-touch-icon" sizes="180x180" href="/images/elephant-companion-180.png" />')
        ->assertDontSeeHtml('rel="shortcut icon"');
});

it('uses a concise primary navigation and a project-focused call to action', function () {
    $response = get(route('home'));

    $response
        ->assertOk()
        ->assertSee('Writing')
        ->assertSee('Discuss a Project')
        ->assertSeeHtml('/images/elephant-companion-128.webp')
        ->assertDontSeeHtml('/images/logo-color.svg');

    $content = responseContent($response->getContent());

    expect($content)
        ->not->toContain('>Contact Me<')
        ->and(substr_count($content, '/images/elephant-companion-128.webp'))
        ->toBe(2);
});

it('places the theme bootstrap inside the document head', function () {
    $response = get(route('home'));

    $response->assertOk();

    $content = responseContent($response->getContent());
    $document = new DOMDocument;
    $document->loadHTML($content, LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING);
    $xpath = new DOMXPath($document);
    $scripts = $xpath->query('//script[contains(., "document.documentElement.classList.remove") and contains(., "localStorage.getItem")]');
    $headScripts = $xpath->query('//head/script[contains(., "document.documentElement.classList.remove") and contains(., "localStorage.getItem")]');

    if (! $scripts instanceof DOMNodeList || ! $headScripts instanceof DOMNodeList) {
        throw new RuntimeException('The markup query failed.');
    }
    expect($scripts->length)->toBe(1)
        ->and($headScripts->length)
        ->toBe(1)
        ->and(stringPosition($content, '<head>'))
        ->toBeLessThan(stringPosition($content, 'Sync theme before paint'))
        ->and(stringPosition($content, 'Sync theme before paint'))
        ->toBeLessThan(stringPosition($content, '</head>'));
});

it('hides inactive podcasts from public podcast surfaces on /', function () {
    // Arrange: the inactive podcast sorts first, so only the active filter keeps it off the page
    Podcast::factory()
        ->inactive()
        ->create(['cover_image_path' => 'podcasts/inactive-cover.webp', 'sort_order' => 0]);
    Podcast::factory()->create(['cover_image_path' => 'podcasts/active-cover.webp', 'sort_order' => 1]);

    // Act
    $response = get(route('home'));

    // Assert
    $response->assertOk()
        ->assertSeeHtml('podcasts/active-cover')
        ->assertDontSeeHtml('podcasts/inactive-cover');
});

it('serves responsive project images while retaining the original fallback on home', function () {
    Storage::fake('public');
    $image = UploadedFile::fake()->image('architecture.png', 1280, 72);
    Storage::disk('public')->put('projects/architecture.png', $image->getContent());
    $project = Project::factory()->published()
        ->featured()
        ->create(['featured_image_path' => 'projects/architecture.png']);
    $response = get(route('home'));
    $response->assertOk()
        ->assertSeeHtml('type="image/webp"')
        ->assertSeeHtml('architecture-640.webp')
        ->assertSeeHtml('architecture-1280.webp')
        ->assertSeeHtml(configuredString(ProjectPresenter::from($project)->featuredImageUrl()));
});

it('keeps public technology and channel details consistent on home', function () {
    $response = get(route('home'));
    $response->assertOk()
        ->assertSeeHtml('https://youtube.com/@thelaravelarchitect')
        ->assertSee('Away from the editor');
});

it('loads public interactivity and typography from the local Vite bundle on home', function () {
    withVite();
    $manifest = assetManifest();
    $response = get(route('home'));
    $response->assertOk()
        ->assertSeeText('Architect')
        ->assertDontSeeHtml('font-empera')
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
});
