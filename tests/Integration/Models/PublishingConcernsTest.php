<?php

use App\Enums\PublishStatus;
use App\Models\Episode;
use App\Models\Post;
use App\Models\Project;
use App\Models\User;
use App\Models\Video;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

it('shares publishing behavior with projects despite their project status enum', function () {
    $project = Project::query()->create([
        'title' => 'Published project',
        'slug' => 'published-project',
        'description' => 'Project description',
        'status' => PublishStatus::Published,
    ]);

    expect($project->isPublished())->toBeTrue()
        ->and(Project::published()->pluck('id')->all())->toBe([$project->id]);
});

it('shares featured behavior across projects and videos', function () {
    $project = Project::query()->create([
        'title' => 'Featured project',
        'slug' => 'featured-project',
        'description' => 'Project description',
        'is_featured' => true,
        'status' => PublishStatus::Draft,
    ]);
    $video = Video::query()->create([
        'youtube_id' => 'featured-video',
        'title' => 'Featured video',
        'is_featured' => true,
    ]);

    expect($project->isFeatured())->toBeTrue()
        ->and($video->isFeatured())->toBeTrue()
        ->and(Project::featured()->pluck('id')->all())->toBe([$project->id])
        ->and(Video::featured()->pluck('id')->all())->toBe([$video->id]);
});

it('shares featured image URL behavior across models', function () {
    Storage::fake('public');

    $post = new Post(['featured_image_path' => 'posts/image.png']);
    $project = new Project(['featured_image_path' => 'projects/image.png']);
    $episode = new Episode(['featured_image_path' => 'episodes/image.png']);

    expect($post->featured_image_url)->toBe(Storage::disk('public')->url('posts/image.png'))
        ->and($project->featured_image_url)->toBe(Storage::disk('public')->url('projects/image.png'))
        ->and($episode->featured_image_url)->toBe(Storage::disk('public')->url('episodes/image.png'));
});

it('preserves publishing behavior for posts', function () {
    $author = User::factory()->create();
    $post = Post::query()->create([
        'title' => 'Published post',
        'slug' => 'published-post',
        'content' => 'Post content',
        'user_id' => $author->id,
        'status' => 'published',
        'published_at' => now()->subMinute(),
    ]);

    expect($post->isPublished())->toBeTrue()
        ->and(Post::published()->pluck('id')->all())->toBe([$post->id]);
});

it('shares date-only publishing behavior with videos', function () {
    $video = Video::query()->create([
        'youtube_id' => 'published-video',
        'title' => 'Published video',
        'published_at' => now()->subMinute(),
    ]);

    expect($video->isPublished())->toBeTrue()
        ->and(Video::published()->pluck('id')->all())->toBe([$video->id]);
});
