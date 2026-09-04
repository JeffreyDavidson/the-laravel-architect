<?php

use App\Filament\Resources\Podcasts\Pages\CreatePodcast;
use App\Filament\Resources\Podcasts\Pages\EditPodcast;
use App\Models\Podcast;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $user = User::factory()->create(['is_admin' => true]);
    $this->actingAs($user);
});

it('creates a podcast through the Filament form', function () {
    livewire(CreatePodcast::class)
        ->fillForm([
            'name' => 'The Architecture Podcast',
            'slug' => 'the-architecture-podcast',
            'description' => 'Conversations about building maintainable software.',
            'long_description' => 'A deeper look at the decisions behind modern Laravel applications.',
            'color' => '#2563eb',
            'apple_url' => 'https://podcasts.apple.com/example',
            'spotify_url' => 'https://open.spotify.com/show/example',
            'rss_url' => 'https://example.test/feed.xml',
            'youtube_url' => 'https://youtube.com/@example',
            'is_active' => true,
            'sort_order' => 2,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Podcast::query()->sole()->only([
        'name',
        'slug',
        'description',
        'long_description',
        'color',
        'apple_url',
        'spotify_url',
        'rss_url',
        'youtube_url',
        'is_active',
        'sort_order',
    ]))->toEqual([
        'name' => 'The Architecture Podcast',
        'slug' => 'the-architecture-podcast',
        'description' => 'Conversations about building maintainable software.',
        'long_description' => 'A deeper look at the decisions behind modern Laravel applications.',
        'color' => '#2563eb',
        'apple_url' => 'https://podcasts.apple.com/example',
        'spotify_url' => 'https://open.spotify.com/show/example',
        'rss_url' => 'https://example.test/feed.xml',
        'youtube_url' => 'https://youtube.com/@example',
        'is_active' => 1,
        'sort_order' => 2,
    ]);
});

it('updates a podcast through the Filament form', function () {
    $podcast = Podcast::query()->create([
        'name' => 'Original podcast',
        'slug' => 'original-podcast',
        'description' => 'Original description.',
        'is_active' => true,
    ]);

    livewire(EditPodcast::class, ['record' => $podcast->getRouteKey()])
        ->fillForm([
            'name' => 'Updated podcast',
            'description' => 'Updated description.',
            'is_active' => false,
            'sort_order' => 4,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($podcast->refresh())
        ->name->toBe('Updated podcast')
        ->and($podcast->description)->toBe('Updated description.')
        ->and($podcast->is_active)->toBeFalse()
        ->and($podcast->sort_order)->toBe(4);
});
