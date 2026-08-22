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
use Illuminate\Support\Facades\Http;

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
    ['/uses', 'Uses'],
    ['/blog', 'Blog'],
    ['/projects', 'Projects'],
    ['/podcasts', 'Podcast'],
]);

it('keeps the admin panel behind authentication', function () {
    $this->get('/admin')->assertRedirect('/admin/login');
    $this->get('/admin/login')->assertOk();
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
