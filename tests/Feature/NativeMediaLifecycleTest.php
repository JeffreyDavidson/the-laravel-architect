<?php

use App\Enums\ProjectStatus;
use App\Enums\PublishStatus;
use App\Models\Episode;
use App\Models\Podcast;
use App\Models\Post;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('public');
});

it('deletes the previous file when native media is replaced', function () {
    Storage::disk('public')->put('projects/old.png', 'old');
    Storage::disk('public')->put('projects/new.png', 'new');

    $project = Project::query()->create([
        'title' => 'Project',
        'slug' => 'project',
        'description' => 'Description',
        'status' => ProjectStatus::Draft,
        'featured_image_path' => 'projects/old.png',
    ]);

    $project->update(['featured_image_path' => 'projects/new.png']);

    Storage::disk('public')->assertMissing('projects/old.png');
    Storage::disk('public')->assertExists('projects/new.png');
});

it('keeps native media when unrelated attributes change', function () {
    Storage::disk('public')->put('projects/image.png', 'image');

    $project = Project::query()->create([
        'title' => 'Project',
        'slug' => 'project',
        'description' => 'Description',
        'status' => ProjectStatus::Draft,
        'featured_image_path' => 'projects/image.png',
    ]);

    $project->update(['title' => 'Renamed Project']);

    Storage::disk('public')->assertExists('projects/image.png');
});

it('deletes native media with its record', function () {
    Storage::disk('public')->put('projects/image.png', 'image');

    $project = Project::query()->create([
        'title' => 'Project',
        'slug' => 'project',
        'description' => 'Description',
        'status' => ProjectStatus::Draft,
        'featured_image_path' => 'projects/image.png',
    ]);

    $project->delete();

    Storage::disk('public')->assertMissing('projects/image.png');
});

it('keeps responsive project image variants in sync with the original image', function () {
    $image = UploadedFile::fake()->image('project.png', 1280, 8)->getContent();
    Storage::disk('public')->put('projects/old.png', $image);
    Storage::disk('public')->put('projects/new.png', $image);

    $project = Project::query()->create([
        'title' => 'Project',
        'slug' => 'project',
        'description' => 'Description',
        'status' => ProjectStatus::Draft,
        'featured_image_path' => 'projects/old.png',
    ]);

    Storage::disk('public')->assertExists([
        'projects/responsive/old-640.webp',
        'projects/responsive/old-1280.webp',
    ]);

    $project->update(['featured_image_path' => 'projects/new.png']);

    Storage::disk('public')->assertMissing([
        'projects/responsive/old-640.webp',
        'projects/responsive/old-1280.webp',
    ]);
    Storage::disk('public')->assertExists([
        'projects/responsive/new-640.webp',
        'projects/responsive/new-1280.webp',
    ]);

    $project->delete();

    Storage::disk('public')->assertMissing([
        'projects/responsive/new-640.webp',
        'projects/responsive/new-1280.webp',
    ]);
});

it('keeps responsive post image variants in sync with the original image', function () {
    $image = UploadedFile::fake()->image('post.png', 1280, 8)->getContent();
    Storage::disk('public')->put('posts/old.png', $image);
    Storage::disk('public')->put('posts/new.png', $image);
    $author = User::factory()->create();

    $post = Post::query()->create([
        'title' => 'Post',
        'slug' => 'post',
        'content' => 'Content',
        'user_id' => $author->id,
        'status' => PublishStatus::Draft,
        'featured_image_path' => 'posts/old.png',
    ]);

    Storage::disk('public')->assertExists([
        'posts/responsive/old-640.webp',
        'posts/responsive/old-1280.webp',
    ]);

    $post->update(['featured_image_path' => 'posts/new.png']);

    Storage::disk('public')->assertMissing([
        'posts/responsive/old-640.webp',
        'posts/responsive/old-1280.webp',
    ]);
    Storage::disk('public')->assertExists([
        'posts/responsive/new-640.webp',
        'posts/responsive/new-1280.webp',
    ]);

    $post->delete();

    Storage::disk('public')->assertMissing([
        'posts/responsive/new-640.webp',
        'posts/responsive/new-1280.webp',
    ]);
});

it('deletes episode media when its podcast is deleted', function () {
    Storage::disk('public')->put('podcasts/cover.png', 'cover');
    Storage::disk('public')->put('episodes/images/episode.png', 'image');
    Storage::disk('public')->put('episodes/audio/episode.mp3', 'audio');

    $podcast = Podcast::query()->create([
        'name' => 'Podcast',
        'slug' => 'podcast',
        'description' => 'Description',
        'cover_image_path' => 'podcasts/cover.png',
    ]);
    Episode::query()->create([
        'podcast_id' => $podcast->id,
        'title' => 'Episode',
        'slug' => 'episode',
        'description' => 'Description',
        'featured_image_path' => 'episodes/images/episode.png',
        'audio_path' => 'episodes/audio/episode.mp3',
    ]);

    $podcast->delete();

    Storage::disk('public')->assertMissing([
        'podcasts/cover.png',
        'episodes/images/episode.png',
        'episodes/audio/episode.mp3',
    ]);
});
