<?php

use App\Enums\PublishStatus;
use App\Models\Episode;
use App\Models\Podcast;
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
    ['/podcasts', 'Podcasts'],
]);

it('keeps the admin panel behind authentication', function () {
    $this->get('/admin')->assertRedirect('/admin/login');
    $this->get('/admin/login')->assertOk();
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
