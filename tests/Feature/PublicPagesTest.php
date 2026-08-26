<?php

use App\Enums\ProjectStatus;
use App\Enums\PublishStatus;
use App\Enums\TestimonialStatus;
use App\Models\Episode;
use App\Models\Podcast;
use App\Models\Post;
use App\Models\Project;
use App\Models\Testimonial;
use App\Models\User;
use App\Models\Video;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

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
    $this->get($uri)
        ->assertOk()
        ->assertSee($copy, false);
})->with([
    ['/', 'The Laravel Architect'],
    ['/about', 'About'],
    ['/contact', 'Contact'],
    ['/privacy', 'Privacy'],
    ['/uses', 'Uses'],
    ['/blog', 'Blog'],
    ['/projects', 'Projects'],
    ['/podcasts', 'Podcast'],
]);

it('keeps one main landmark on public index pages', function (string $routeName) {
    $content = $this->get(route($routeName))
        ->assertOk()
        ->getContent();

    expect(substr_count($content, '<main'))->toBe(1)
        ->and(substr_count($content, '</main>'))->toBe(1);
})->with([
    'projects' => 'projects.index',
    'podcasts' => 'podcast.index',
]);

it('uses a concise primary navigation and a project-focused call to action', function () {
    $content = $this->get(route('home'))
        ->assertOk()
        ->assertSee('Writing')
        ->assertSee('Discuss a Project')
        ->getContent();

    expect($content)->not->toContain('>Contact Me<');
});

it('keeps public technology and channel details consistent', function () {
    $this->get(route('about'))
        ->assertOk()
        ->assertSee((string) config('public-site.technology.laravel'))
        ->assertSee('I share practical Laravel videos');

    $this->get(route('uses'))
        ->assertOk()
        ->assertSee('Laravel '.config('public-site.technology.laravel'))
        ->assertSee('Filament '.config('public-site.technology.filament'));

    $this->get(route('home'))
        ->assertOk()
        ->assertSee(config('public-site.youtube.url'), false)
        ->assertSee('Practical Laravel, on video');
});

it('places the mobile uses jump navigation before the equipment list', function () {
    $content = $this->get(route('uses'))
        ->assertOk()
        ->assertSee('aria-label="Jump to uses section"', false)
        ->getContent();

    expect(strpos($content, 'aria-label="Jump to uses section"'))
        ->toBeLessThan(strpos($content, 'id="hardware"'));
});

it('links the privacy notice from public collection points', function () {
    $privacyUrl = route('privacy');

    $this->get(route('testimonials.create'))
        ->assertOk()
        ->assertSee($privacyUrl, false)
        ->assertSee('Approved submissions may be displayed publicly');

    $this->get(route('contact'))
        ->assertOk()
        ->assertSee($privacyUrl, false)
        ->assertSee('Your details are used to reply to this inquiry.');
});

it('loads public interactivity and typography from the local Vite bundle', function () {
    $this->withVite();

    $manifest = json_decode(
        file_get_contents(public_path('build/manifest.json')),
        true,
        flags: JSON_THROW_ON_ERROR,
    );

    $this->get(route('home'))
        ->assertOk()
        ->assertDontSee('cdn.jsdelivr.net/npm/alpinejs', false)
        ->assertDontSee('fonts.bunny.net', false)
        ->assertDontSee($manifest['resources/css/filament/admin/theme.css']['file'], false)
        ->assertSee($manifest['resources/css/app.css']['file'], false)
        ->assertSee($manifest['resources/css/pages/home-entry.css']['file'], false)
        ->assertDontSee($manifest['resources/css/pages/about-entry.css']['file'], false)
        ->assertDontSee($manifest['resources/css/pages/listings-entry.css']['file'], false)
        ->assertSee($manifest['resources/js/app.js']['file'], false);

    $this->get(route('about'))
        ->assertOk()
        ->assertSee($manifest['resources/css/app.css']['file'], false)
        ->assertSee($manifest['resources/css/pages/about-entry.css']['file'], false)
        ->assertDontSee($manifest['resources/css/pages/home-entry.css']['file'], false)
        ->assertDontSee($manifest['resources/css/pages/listings-entry.css']['file'], false)
        ->assertDontSee($manifest['resources/css/pages/podcast-entry.css']['file'], false);

    $this->get(route('blog.index'))
        ->assertOk()
        ->assertSee($manifest['resources/css/app.css']['file'], false)
        ->assertSee($manifest['resources/css/pages/listings-entry.css']['file'], false)
        ->assertDontSee($manifest['resources/css/pages/about-entry.css']['file'], false)
        ->assertDontSee($manifest['resources/css/pages/home-entry.css']['file'], false)
        ->assertDontSee($manifest['resources/css/pages/podcast-entry.css']['file'], false);

    $this->get(route('projects.index'))
        ->assertOk()
        ->assertSee($manifest['resources/css/app.css']['file'], false)
        ->assertSee($manifest['resources/css/pages/listings-entry.css']['file'], false)
        ->assertDontSee($manifest['resources/css/pages/about-entry.css']['file'], false)
        ->assertDontSee($manifest['resources/css/pages/home-entry.css']['file'], false)
        ->assertDontSee($manifest['resources/css/pages/podcast-entry.css']['file'], false);

    $this->get(route('podcast.index'))
        ->assertOk()
        ->assertSee($manifest['resources/css/app.css']['file'], false)
        ->assertSee($manifest['resources/css/pages/podcast-entry.css']['file'], false)
        ->assertDontSee($manifest['resources/css/pages/about-entry.css']['file'], false)
        ->assertDontSee($manifest['resources/css/pages/listings-entry.css']['file'], false)
        ->assertDontSee($manifest['resources/css/pages/home-entry.css']['file'], false);

    expect(implode("\n", array_column($manifest, 'file')))
        ->not->toContain('Empera-Vintage');
});

it('renders an accessible homepage architecture scene with a static fallback', function () {
    $content = $this->get(route('home'))
        ->assertOk()
        ->assertSee('data-architecture-scene', false)
        ->assertSee('aria-labelledby="architecture-title architecture-description"', false)
        ->assertSee('data-architecture-fallback', false)
        ->assertSee('Request', false)
        ->assertSee('Domain', false)
        ->assertSee('Data', false)
        ->getContent();

    expect(substr_count($content, 'data-architecture-scene'))->toBe(1)
        ->and($content)->not->toContain('id="code-editor"')
        ->and($content)->not->toContain('role="tablist"');
});

it('places the theme bootstrap inside the document head', function () {
    $content = $this->get(route('home'))
        ->assertOk()
        ->getContent();

    expect(strpos($content, '<head>'))
        ->toBeLessThan(strpos($content, 'Sync theme before paint'));
});

it('renders accessible podcast episode embeds and external links', function () {
    $podcast = Podcast::query()->create([
        'name' => 'Architecture Sessions',
        'slug' => 'architecture-sessions',
        'description' => 'Conversations about Laravel architecture.',
        'is_active' => true,
    ]);
    $episode = Episode::query()->create([
        'podcast_id' => $podcast->id,
        'title' => 'Designing Laravel Applications',
        'slug' => 'designing-laravel-applications',
        'description' => 'A practical architecture discussion.',
        'youtube_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
        'status' => PublishStatus::Published,
        'published_at' => now()->subDay(),
    ]);

    $this->get(route('podcast.episode', [$podcast, $episode]))
        ->assertOk()
        ->assertSee('title="Designing Laravel Applications on YouTube"', false)
        ->assertSee('loading="lazy"', false)
        ->assertSee('rel="noopener noreferrer"', false)
        ->assertSee('data-podcast-copy-url=', false)
        ->assertDontSee('onclick=', false);
});

it('renders keyboard accessible podcast audio controls', function () {
    $podcast = Podcast::query()->create([
        'name' => 'Architecture Sessions',
        'slug' => 'architecture-sessions',
        'description' => 'Conversations about Laravel architecture.',
        'is_active' => true,
    ]);
    $episode = Episode::query()->create([
        'podcast_id' => $podcast->id,
        'title' => 'Accessible Audio',
        'slug' => 'accessible-audio',
        'description' => 'An episode with accessible playback controls.',
        'audio_url' => 'https://example.com/accessible-audio.mp3',
        'status' => PublishStatus::Published,
        'published_at' => now()->subDay(),
    ]);

    $this->get(route('podcast.episode', [$podcast, $episode]))
        ->assertOk()
        ->assertSee('aria-label="Seek episode"', false)
        ->assertSee('aria-label="Skip back 15 seconds"', false)
        ->assertSee('aria-label="Skip forward 30 seconds"', false)
        ->assertSee(':aria-label="playing ? \'Pause episode\' : \'Play episode\'"', false)
        ->assertDontSee('@click="seek($event)"', false);
});

it('keeps the admin panel behind authentication', function () {
    $this->withVite();

    $manifest = json_decode(
        file_get_contents(public_path('build/manifest.json')),
        true,
        flags: JSON_THROW_ON_ERROR,
    );

    $this->get('/admin')->assertRedirect('/admin/login');
    $this->get('/admin/login')
        ->assertOk()
        ->assertSee($manifest['resources/css/filament/admin/theme.css']['file'], false);
});

it('uses published work and approved recommendations as homepage proof', function () {
    $author = User::factory()->create();

    Post::query()->create([
        'title' => 'Published architecture notes',
        'slug' => 'published-architecture-notes',
        'content' => 'Useful Laravel architecture notes.',
        'user_id' => $author->id,
        'status' => PublishStatus::Published,
        'published_at' => now()->subDay(),
    ]);

    Post::query()->create([
        'title' => 'Unpublished architecture notes',
        'slug' => 'unpublished-architecture-notes',
        'content' => 'A draft that should not count.',
        'user_id' => $author->id,
        'status' => PublishStatus::Draft,
    ]);

    Project::query()->create([
        'title' => 'Published Laravel application',
        'slug' => 'published-laravel-application',
        'description' => 'A published project.',
        'status' => ProjectStatus::Published,
    ]);

    Project::query()->create([
        'title' => 'Draft Laravel application',
        'slug' => 'draft-laravel-application',
        'description' => 'A draft project that should not count.',
        'status' => ProjectStatus::Draft,
    ]);

    Testimonial::query()->create([
        'name' => 'Approved Client',
        'body' => 'A trusted Laravel partner.',
        'status' => TestimonialStatus::Approved,
    ]);

    Testimonial::query()->create([
        'name' => 'Pending Client',
        'body' => 'This recommendation is not approved yet.',
        'status' => TestimonialStatus::Pending,
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertViewHas('publishedPostCount', 1)
        ->assertViewHas('publishedProjectCount', 1)
        ->assertSee('Approved Client')
        ->assertDontSee('Pending Client')
        ->assertSeeInOrder([
            'Years building PHP',
            'Published articles',
            'Portfolio projects',
            'Recommendations',
        ]);
});

it('presents published projects as case studies without inferring product status', function () {
    $project = Project::query()->create([
        'title' => 'Architecture Decisions',
        'slug' => 'architecture-decisions',
        'description' => 'A project shaped by explicit technical tradeoffs.',
        'content' => '## The challenge\n\nModel a complex domain without hiding its rules.',
        'github_url' => 'https://github.com/example/architecture-decisions',
        'tech_stack' => ['Laravel', 'Pest'],
        'is_featured' => true,
        'status' => ProjectStatus::Published,
    ]);

    $this->get(route('projects.index'))
        ->assertOk()
        ->assertSee('Laravel case studies, not just screenshots')
        ->assertSee('Read the case study')
        ->assertSee($project->title);

    $this->get(route('projects.show', $project))
        ->assertOk()
        ->assertSee('Project case study')
        ->assertSee('Decisions over decoration')
        ->assertSee('The challenge')
        ->assertDontSee('Active');
});

it('serves responsive project images while retaining the original fallback', function () {
    Storage::fake('public');
    $image = UploadedFile::fake()->image('architecture.png', 1280, 72);
    Storage::disk('public')->put('projects/architecture.png', $image->getContent());

    $project = Project::query()->create([
        'title' => 'Responsive Architecture',
        'slug' => 'responsive-architecture',
        'description' => 'A project with responsive imagery.',
        'is_featured' => true,
        'status' => ProjectStatus::Published,
        'featured_image_path' => 'projects/architecture.png',
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('type="image/webp"', false)
        ->assertSee('architecture-640.webp', false)
        ->assertSee('architecture-1280.webp', false)
        ->assertSee($project->featured_image_url, false);

    $this->get(route('projects.show', $project))
        ->assertOk()
        ->assertSee('type="image/webp"', false)
        ->assertSee('fetchpriority="high"', false)
        ->assertSee($project->featured_image_url, false);
});

it('shows synced published YouTube videos without stale launch content', function () {
    $publishedVideo = Video::query()->create([
        'youtube_id' => 'published-video',
        'title' => 'Modern Laravel Architecture',
        'slug' => 'modern-laravel-architecture',
        'thumbnail_url' => 'https://example.com/video.jpg',
        'duration' => 'PT12M34S',
        'published_at' => now()->subDay(),
    ]);
    $futureVideo = Video::query()->create([
        'youtube_id' => 'future-video',
        'title' => 'Future Laravel Video',
        'slug' => 'future-laravel-video',
        'published_at' => now()->addDay(),
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee($publishedVideo->title)
        ->assertSee($publishedVideo->youtube_url)
        ->assertDontSee($futureVideo->title)
        ->assertDontSee('Launching March 2')
        ->assertDontSee('Coming to the Channel')
        ->assertDontSee('before launch day');
});

it('hides inactive podcasts from public podcast surfaces', function () {
    $activePodcast = Podcast::query()->create([
        'name' => 'Coffee With The Laravel Architect',
        'slug' => 'coffee-with-the-laravel-architect',
        'description' => 'Laravel conversations',
        'is_active' => true,
    ]);

    $inactivePodcast = Podcast::query()->create([
        'name' => 'Embracing Cloudy Days',
        'slug' => 'embracing-cloudy-days',
        'description' => 'Personal essays',
        'is_active' => false,
    ]);

    $episode = Episode::query()->create([
        'podcast_id' => $inactivePodcast->id,
        'title' => 'Welcome to the Clouds',
        'slug' => 'welcome-to-the-clouds',
        'description' => 'A preserved inactive episode',
        'status' => PublishStatus::Published,
        'published_at' => now()->subDay(),
    ]);

    $this->get('/podcasts')
        ->assertOk()
        ->assertSee($activePodcast->name)
        ->assertDontSee($inactivePodcast->name)
        ->assertDontSee('Real Talk on Hard Days');

    $this->get('/')
        ->assertOk()
        ->assertDontSee($inactivePodcast->name)
        ->assertDontSee('toHaveCount</span>(<span class="syn-variable">2</span>', false);

    $this->get(route('podcast.show', $inactivePodcast))->assertNotFound();
    $this->get(route('podcast.episode', [$inactivePodcast, $episode]))->assertNotFound();
});
