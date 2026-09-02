<?php

use App\Models\Video;
use App\Services\YouTubeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use JMac\Testing\Double;

uses(RefreshDatabase::class);

it('creates new videos and forwards the requested limit', function () {
    $youtube = Double::for(YouTubeService::class);
    $youtube->expects('getChannelVideos')
        ->with(12)
        ->returns([[
            'youtube_id' => 'new-video',
            'title' => 'A New Video',
            'description' => 'New description',
            'thumbnail_url' => 'https://example.com/new.jpg',
            'duration' => 'PT8M30S',
            'view_count' => 120,
            'like_count' => 15,
            'comment_count' => 4,
            'published_at' => '2026-08-18T12:00:00Z',
        ]]);
    app()->instance(YouTubeService::class, $youtube);

    $this->artisan('youtube:sync', ['--limit' => 12])
        ->expectsOutput('Fetching videos from YouTube...')
        ->expectsOutput('Done! 1 new, 0 updated.')
        ->assertSuccessful();

    $video = Video::query()->sole();

    expect($video->youtube_id)->toBe('new-video')
        ->and($video->title)->toBe('A New Video')
        ->and($video->slug)->toBe('a-new-video')
        ->and($video->description)->toBe('New description')
        ->and($video->view_count)->toBe(120)
        ->and($video->published_at?->toIso8601String())->toBe('2026-08-18T12:00:00+00:00')
        ->and($video->synced_at)->not->toBeNull();
});

it('updates an existing video without replacing its publishing fields', function () {
    $this->travelTo('2026-08-19 10:30:00');

    $video = Video::query()->create([
        'youtube_id' => 'existing-video',
        'title' => 'Old Title',
        'slug' => 'curated-slug',
        'description' => 'Old description',
        'view_count' => 10,
        'published_at' => '2026-08-01 09:00:00',
        'is_featured' => true,
    ]);

    $youtube = Double::for(YouTubeService::class);
    $youtube->expects('getChannelVideos')
        ->with(50)
        ->returns([[
            'youtube_id' => 'existing-video',
            'title' => 'Updated Title',
            'description' => 'Updated description',
            'thumbnail_url' => 'https://example.com/updated.jpg',
            'duration' => 'PT10M',
            'view_count' => 250,
            'like_count' => 25,
            'comment_count' => 5,
            'published_at' => '2026-08-18T12:00:00Z',
        ]]);
    app()->instance(YouTubeService::class, $youtube);

    $this->artisan('youtube:sync')
        ->expectsOutput('Done! 0 new, 1 updated.')
        ->assertSuccessful();

    $video->refresh();

    expect($video->title)->toBe('Updated Title')
        ->and($video->description)->toBe('Updated description')
        ->and($video->view_count)->toBe(250)
        ->and($video->slug)->toBe('curated-slug')
        ->and($video->is_featured)->toBeTrue()
        ->and($video->published_at?->toDateTimeString())->toBe('2026-08-01 09:00:00')
        ->and($video->synced_at?->toDateTimeString())->toBe('2026-08-19 10:30:00');
});

it('creates stable unique slugs for colliding and empty titles', function () {
    $payloads = [
        [
            'youtube_id' => 'first-video',
            'title' => 'Same Title',
            'description' => null,
            'thumbnail_url' => null,
            'duration' => null,
            'view_count' => 0,
            'like_count' => 0,
            'comment_count' => 0,
            'published_at' => null,
        ],
        [
            'youtube_id' => 'second-video',
            'title' => 'Same Title',
            'description' => null,
            'thumbnail_url' => null,
            'duration' => null,
            'view_count' => 0,
            'like_count' => 0,
            'comment_count' => 0,
            'published_at' => null,
        ],
        [
            'youtube_id' => 'empty-title-video',
            'title' => '!!!',
            'description' => null,
            'thumbnail_url' => null,
            'duration' => null,
            'view_count' => 0,
            'like_count' => 0,
            'comment_count' => 0,
            'published_at' => null,
        ],
    ];

    $youtube = Double::for(YouTubeService::class);
    $youtube->expects('getChannelVideos')
        ->times(2)
        ->with(50)
        ->returns($payloads);
    app()->instance(YouTubeService::class, $youtube);

    $this->artisan('youtube:sync')
        ->expectsOutput('Done! 3 new, 0 updated.')
        ->assertSuccessful();

    expect(Video::query()->where('youtube_id', 'first-video')->value('slug'))->toBe('same-title')
        ->and(Video::query()->where('youtube_id', 'second-video')->value('slug'))->toBe('same-title-second-video')
        ->and(Video::query()->where('youtube_id', 'empty-title-video')->value('slug'))->toBe('video-empty-title-video');

    $this->artisan('youtube:sync')
        ->expectsOutput('Done! 0 new, 3 updated.')
        ->assertSuccessful();

    expect(Video::query()->count())->toBe(3)
        ->and(Video::query()->where('youtube_id', 'first-video')->value('slug'))->toBe('same-title')
        ->and(Video::query()->where('youtube_id', 'second-video')->value('slug'))->toBe('same-title-second-video')
        ->and(Video::query()->where('youtube_id', 'empty-title-video')->value('slug'))->toBe('video-empty-title-video');
});

it('fails without changing videos when YouTube is unavailable', function () {
    $video = Video::query()->create([
        'youtube_id' => 'existing-video',
        'title' => 'Existing Title',
        'slug' => 'existing-title',
    ]);

    $youtube = Double::for(YouTubeService::class);
    $youtube->expects('getChannelVideos')
        ->with(50)
        ->throws(new RuntimeException('YouTube is unavailable.'));
    app()->instance(YouTubeService::class, $youtube);

    $this->artisan('youtube:sync')
        ->expectsOutput('YouTube is unavailable.')
        ->assertFailed();

    expect($video->refresh()->title)->toBe('Existing Title')
        ->and(Video::query()->count())->toBe(1);
});
