<?php

use App\Models\Video;
use App\Services\YouTubeService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('succeeds without calling YouTube when there are no videos', function () {
    $youtube = Mockery::mock(YouTubeService::class);
    $youtube->shouldNotReceive('getStatsForVideos');
    app()->instance(YouTubeService::class, $youtube);

    $this->artisan('youtube:stats')
        ->expectsOutput('No videos to update. Run youtube:sync first.')
        ->assertSuccessful();
});

it('updates video statistics in batches of fifty', function () {
    $this->travelTo('2026-08-19 11:45:00');

    foreach (range(1, 51) as $index) {
        Video::query()->create([
            'youtube_id' => "video-{$index}",
            'title' => "Video {$index}",
            'slug' => "video-{$index}",
            'view_count' => $index,
            'like_count' => $index,
            'comment_count' => $index,
        ]);
    }

    $firstBatch = array_map(fn (int $index): string => "video-{$index}", range(1, 50));

    $youtube = Mockery::mock(YouTubeService::class);
    $youtube->shouldReceive('getStatsForVideos')
        ->once()
        ->with($firstBatch)
        ->andReturn([
            'video-1' => [
                'view_count' => 1_000,
                'like_count' => 100,
                'comment_count' => 10,
            ],
        ])
        ->ordered();
    $youtube->shouldReceive('getStatsForVideos')
        ->once()
        ->with(['video-51'])
        ->andReturn([
            'video-51' => [
                'view_count' => 5_100,
                'like_count' => 510,
                'comment_count' => 51,
            ],
        ])
        ->ordered();
    app()->instance(YouTubeService::class, $youtube);

    $this->artisan('youtube:stats')
        ->expectsOutput('Updating stats for 51 videos...')
        ->expectsOutput('Updated stats for 2 videos.')
        ->assertSuccessful();

    $firstVideo = Video::query()->where('youtube_id', 'video-1')->sole();
    $lastVideo = Video::query()->where('youtube_id', 'video-51')->sole();
    $unchangedVideo = Video::query()->where('youtube_id', 'video-2')->sole();

    expect($firstVideo->view_count)->toBe(1_000)
        ->and($firstVideo->like_count)->toBe(100)
        ->and($firstVideo->comment_count)->toBe(10)
        ->and($firstVideo->synced_at?->toDateTimeString())->toBe('2026-08-19 11:45:00')
        ->and($lastVideo->view_count)->toBe(5_100)
        ->and($lastVideo->like_count)->toBe(510)
        ->and($lastVideo->comment_count)->toBe(51)
        ->and($lastVideo->synced_at?->toDateTimeString())->toBe('2026-08-19 11:45:00')
        ->and($unchangedVideo->view_count)->toBe(2)
        ->and($unchangedVideo->synced_at)->toBeNull();
});

it('fails without changing statistics when YouTube is unavailable', function () {
    $video = Video::query()->create([
        'youtube_id' => 'existing-video',
        'title' => 'Existing Video',
        'slug' => 'existing-video',
        'view_count' => 25,
        'like_count' => 5,
        'comment_count' => 2,
    ]);

    $youtube = Mockery::mock(YouTubeService::class);
    $youtube->shouldReceive('getStatsForVideos')
        ->once()
        ->with(['existing-video'])
        ->andThrow(new RuntimeException('YouTube is unavailable.'));
    app()->instance(YouTubeService::class, $youtube);

    $this->artisan('youtube:stats')
        ->expectsOutput('YouTube is unavailable.')
        ->assertFailed();

    $video->refresh();

    expect($video->view_count)->toBe(25)
        ->and($video->like_count)->toBe(5)
        ->and($video->comment_count)->toBe(2)
        ->and($video->synced_at)->toBeNull();
});
