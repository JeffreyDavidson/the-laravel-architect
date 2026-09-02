<?php

use App\Enums\ProjectStatus;
use App\Enums\PublishStatus;
use App\Models\Podcast;
use App\Models\Post;
use App\Models\Project;
use App\Models\User;
use App\Services\ResponsiveImageVariants;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('public');
});

it('repairs unhealthy responsive variants across every supported media type', function () {
    $projectImage = UploadedFile::fake()->image('project.png', 800, 45)->getContent();
    $postImage = UploadedFile::fake()->image('post.png', 1280, 72)->getContent();
    $podcastImage = UploadedFile::fake()->image('podcast.png', 1280, 72)->getContent();
    Storage::disk('public')->put('projects/project.png', $projectImage);
    Storage::disk('public')->put('projects/responsive/project-1280.webp', 'obsolete');
    Storage::disk('public')->put('posts/post.png', $postImage);
    Storage::disk('public')->put('podcasts/podcast.png', $podcastImage);

    Project::withoutEvents(fn () => Project::query()->create([
        'title' => 'Project',
        'slug' => 'project',
        'description' => 'Description',
        'status' => ProjectStatus::Published,
        'featured_image_path' => 'projects/project.png',
    ]));
    Post::withoutEvents(fn () => Post::query()->create([
        'title' => 'Post',
        'slug' => 'post',
        'content' => 'Content',
        'user_id' => User::factory()->create()->id,
        'status' => PublishStatus::Published,
        'published_at' => now(),
        'featured_image_path' => 'posts/post.png',
    ]));
    Podcast::withoutEvents(fn () => Podcast::query()->create([
        'name' => 'Podcast',
        'slug' => 'podcast',
        'description' => 'Description',
        'cover_image_path' => 'podcasts/podcast.png',
    ]));
    app(ResponsiveImageVariants::class)->generate('podcasts/podcast.png');

    $this->artisan('media:repair-responsive-images')
        ->expectsOutputToContain('Generated responsive images for 1 project.')
        ->expectsOutputToContain('Generated responsive images for 1 post.')
        ->expectsOutputToContain('Generated responsive images for 0 podcasts.')
        ->expectsOutputToContain('Skipped 1 already verified podcast.')
        ->expectsOutputToContain('Responsive image verification passed.')
        ->expectsOutputToContain('Responsive image repair completed successfully.')
        ->assertSuccessful();

    Storage::disk('public')->assertExists([
        'projects/responsive/project-640.webp',
        'posts/responsive/post-640.webp',
        'posts/responsive/post-1280.webp',
        'podcasts/responsive/podcast-640.webp',
        'podcasts/responsive/podcast-1280.webp',
    ]);
    Storage::disk('public')->assertMissing('projects/responsive/project-1280.webp');

    $this->artisan('media:repair-responsive-images', ['--force' => true])
        ->expectsOutputToContain('Generated responsive images for 1 project.')
        ->expectsOutputToContain('Generated responsive images for 1 post.')
        ->expectsOutputToContain('Generated responsive images for 1 podcast.')
        ->doesntExpectOutputToContain('already verified')
        ->expectsOutputToContain('Responsive image verification passed.')
        ->assertSuccessful();
});

it('repairs remaining media types before reporting a failure', function () {
    $postImage = UploadedFile::fake()->image('post.png', 1280, 72)->getContent();
    Storage::disk('public')->put('projects/project.png', 'unsupported');
    Storage::disk('public')->put('posts/post.png', $postImage);

    Project::withoutEvents(fn () => Project::query()->create([
        'title' => 'Project',
        'slug' => 'project',
        'description' => 'Description',
        'status' => ProjectStatus::Published,
        'featured_image_path' => 'projects/project.png',
    ]));
    Post::withoutEvents(fn () => Post::query()->create([
        'title' => 'Post',
        'slug' => 'post',
        'content' => 'Content',
        'user_id' => User::factory()->create()->id,
        'status' => PublishStatus::Published,
        'published_at' => now(),
        'featured_image_path' => 'posts/post.png',
    ]));

    $this->artisan('media:repair-responsive-images')
        ->expectsOutputToContain('Generated responsive images for 1 post.')
        ->expectsOutputToContain('Responsive image verification failed.')
        ->expectsOutputToContain('Responsive image repair completed with failures.')
        ->assertFailed();

    Storage::disk('public')->assertExists([
        'posts/responsive/post-640.webp',
        'posts/responsive/post-1280.webp',
    ]);
});

it('refuses to overlap another responsive image repair', function () {
    $lock = Cache::lock('framework/command-media:repair-responsive-images', 60);

    expect($lock->get())->toBeTrue();

    try {
        $this->artisan('media:repair-responsive-images')
            ->expectsOutputToContain('The [media:repair-responsive-images] command is already running.')
            ->assertFailed();
    } finally {
        $lock->release();
    }
});
