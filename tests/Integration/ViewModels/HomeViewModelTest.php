<?php

use App\Models\Project;
use App\Models\Video;
use App\ViewModels\HomeViewModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

it('builds the bounded public homepage payload', function () {
    foreach (range(1, 5) as $sortOrder) {
        createHomeViewModelProject($sortOrder);
    }
    Project::factory()
        ->featured()
        ->create();

    foreach (range(1, 4) as $sortOrder) {
        createHomeViewModelVideo($sortOrder);
    }

    createHomeViewModelVideo(5, now()->addDay());

    $data = app(HomeViewModel::class)
        ->data();

    expect($data)->toHaveKeys([
        'latestPosts',
        'featuredProjects',
        'podcast',
        'latestYouTubeVideos',
        'youtubeProfileUrl',
        'publishedPostCount',
        'publishedProjectCount',
        'seoSource',
    ])
        ->and($data['latestPosts'])
        ->toBeEmpty()
        ->and($data['podcast'])
        ->toBeNull()
        ->and($data['featuredProjects']->pluck('sort_order')
            ->all())
        ->toBe([1, 2, 3, 4])
        ->and($data['latestYouTubeVideos']->pluck('youtube_id')
            ->all())
        ->toBe(['video-1', 'video-2', 'video-3'])
        ->and($data['youtubeProfileUrl'])
        ->toBe('https://youtube.com/@thelaravelarchitect')
        ->and($data['publishedPostCount'])
        ->toBe(0)
        ->and($data['publishedProjectCount'])
        ->toBe(5);
});

function createHomeViewModelProject(int $sortOrder): void
{
    Project::factory()
        ->featured()
        ->published()
        ->create(['sort_order' => $sortOrder]);
}

function createHomeViewModelVideo(int $position, ?DateTimeInterface $publishedAt = null): void
{
    Video::factory()->create([
        'youtube_id' => "video-{$position}",
        'published_at' => $publishedAt ?? now()->subDays($position),
    ]);
}
