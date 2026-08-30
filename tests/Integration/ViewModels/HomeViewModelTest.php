<?php

use App\Enums\ProjectStatus;
use App\Enums\TestimonialStatus;
use App\Models\Project;
use App\Models\Testimonial;
use App\Models\Video;
use App\ViewModels\HomeViewModel;
use DateTimeInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

uses(RefreshDatabase::class);

it('builds the bounded public homepage payload', function () {
    Cache::put('youtube.subscriber_count', 4242);

    foreach (range(1, 5) as $sortOrder) {
        createHomeViewModelProject($sortOrder);
    }

    Project::query()->create([
        'title' => 'Draft project',
        'slug' => 'draft-project',
        'description' => 'This project is not public.',
        'is_featured' => true,
        'sort_order' => 0,
        'status' => ProjectStatus::Draft,
    ]);

    foreach (range(1, 4) as $sortOrder) {
        createHomeViewModelTestimonial($sortOrder);
        createHomeViewModelVideo($sortOrder);
    }

    Testimonial::query()->create([
        'name' => 'Pending client',
        'body' => 'This recommendation is not approved.',
        'status' => TestimonialStatus::Pending,
        'sort_order' => 0,
    ]);
    createHomeViewModelVideo(5, now()->addDay());

    $data = app(HomeViewModel::class)
        ->data();

    expect($data)->toHaveKeys([
        'latestPosts',
        'featuredProjects',
        'youtubeSubscribers',
        'latestYouTubeVideos',
        'testimonials',
        'publishedPostCount',
        'publishedProjectCount',
        'approvedTestimonialCount',
        'seoSource',
    ])
        ->and($data['latestPosts'])->toBeEmpty()
        ->and($data['featuredProjects']->pluck('sort_order')->all())->toBe([1, 2, 3, 4])
        ->and($data['youtubeSubscribers'])->toBe(4242)
        ->and($data['latestYouTubeVideos']->pluck('youtube_id')->all())->toBe(['video-1', 'video-2', 'video-3'])
        ->and($data['testimonials']->pluck('sort_order')->all())->toBe([1, 2, 3])
        ->and($data['publishedPostCount'])->toBe(0)
        ->and($data['publishedProjectCount'])->toBe(5)
        ->and($data['approvedTestimonialCount'])->toBe(4);
});

function createHomeViewModelProject(int $sortOrder): void
{
    Project::query()->create([
        'title' => "Published project {$sortOrder}",
        'slug' => "published-project-{$sortOrder}",
        'description' => "Description for published project {$sortOrder}.",
        'is_featured' => true,
        'sort_order' => $sortOrder,
        'status' => ProjectStatus::Published,
    ]);
}

function createHomeViewModelTestimonial(int $sortOrder): void
{
    Testimonial::query()->create([
        'name' => "Approved client {$sortOrder}",
        'body' => "Approved recommendation {$sortOrder}.",
        'status' => TestimonialStatus::Approved,
        'sort_order' => $sortOrder,
    ]);
}

function createHomeViewModelVideo(int $position, ?DateTimeInterface $publishedAt = null): void
{
    Video::query()->create([
        'youtube_id' => "video-{$position}",
        'title' => "Video {$position}",
        'slug' => "video-{$position}",
        'published_at' => $publishedAt ?? now()->subDays($position),
    ]);
}
