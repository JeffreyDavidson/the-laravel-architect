<?php

use App\Enums\PublishStatus;
use App\Models\Episode;
use App\Models\Podcast;
use App\Models\Post;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use JMac\Testing\Double;
use Psr\Log\LoggerInterface;

pest()->use(RefreshDatabase::class);

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
        'status' => PublishStatus::Draft,
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
        'status' => PublishStatus::Draft,
        'featured_image_path' => 'projects/image.png',
    ]);

    $project->update(['title' => 'Renamed Project']);

    Storage::disk('public')->assertExists('projects/image.png');
});

it('keeps replaced native media when the transaction rolls back', function () {
    Storage::disk('public')->put('projects/old.png', 'old');
    Storage::disk('public')->put('projects/new.png', 'new');

    $project = Project::query()->create([
        'title' => 'Project',
        'slug' => 'project',
        'description' => 'Description',
        'status' => PublishStatus::Draft,
        'featured_image_path' => 'projects/old.png',
    ]);

    DB::beginTransaction();
    $project->update(['featured_image_path' => 'projects/new.png']);
    DB::rollBack();

    Storage::disk('public')->assertExists('projects/old.png');
    Storage::disk('public')->assertExists('projects/new.png');
});

it('preserves original images and responsive variants when replacement rolls back', function () {
    $image = UploadedFile::fake()->image('project.png', 1280, 8)->getContent();
    Storage::disk('public')->put('projects/original.png', $image);
    Storage::disk('public')->put('projects/replacement.png', $image);
    $project = Project::query()->create([
        'title' => 'Responsive rollback',
        'description' => 'Preserve committed media.',
        'status' => PublishStatus::Draft,
        'featured_image_path' => 'projects/original.png',
    ]);

    DB::beginTransaction();
    $project->update(['featured_image_path' => 'projects/replacement.png']);
    DB::rollBack();

    expect($project->refresh()->featured_image_path)->toBe('projects/original.png');
    Storage::disk('public')->assertExists([
        'projects/original.png',
        'projects/responsive/original-640.webp',
        'projects/responsive/original-1280.webp',
    ]);
});

it('preserves cached OG images when post deletion rolls back', function () {
    Storage::fake('local');
    $post = Post::query()->create([
        'title' => 'OG rollback',
        'content' => 'Preserve the committed post.',
        'user_id' => User::factory()->create()->getKey(),
        'status' => PublishStatus::Draft,
    ]);
    $path = "og-images/{$post->id}/cached.png";
    Storage::disk('local')->put($path, 'cached image');

    DB::beginTransaction();
    $post->delete();
    DB::rollBack();

    expect(Post::query()->whereKey($post->getKey())->exists())->toBeTrue();
    Storage::disk('local')->assertExists($path);
});

it('deletes replaced native media after the transaction commits', function () {
    Storage::disk('public')->put('projects/old.png', 'old');
    Storage::disk('public')->put('projects/new.png', 'new');

    $project = Project::query()->create([
        'title' => 'Project',
        'slug' => 'project',
        'description' => 'Description',
        'status' => PublishStatus::Draft,
        'featured_image_path' => 'projects/old.png',
    ]);

    DB::beginTransaction();
    $project->update(['featured_image_path' => 'projects/new.png']);
    DB::commit();

    Storage::disk('public')->assertMissing('projects/old.png');
    Storage::disk('public')->assertExists('projects/new.png');
});

it('deletes native media with its record', function () {
    Storage::disk('public')->put('projects/image.png', 'image');

    $project = Project::query()->create([
        'title' => 'Project',
        'slug' => 'project',
        'description' => 'Description',
        'status' => PublishStatus::Draft,
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
        'status' => PublishStatus::Draft,
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

it('keeps responsive podcast cover variants in sync with the original image', function () {
    $image = UploadedFile::fake()->image('podcast.png', 1280, 8)->getContent();
    Storage::disk('public')->put('podcasts/old.png', $image);
    Storage::disk('public')->put('podcasts/new.png', $image);

    $podcast = Podcast::query()->create([
        'name' => 'Podcast',
        'slug' => 'podcast',
        'description' => 'Description',
        'cover_image_path' => 'podcasts/old.png',
    ]);

    Storage::disk('public')->assertExists([
        'podcasts/responsive/old-640.webp',
        'podcasts/responsive/old-1280.webp',
    ]);

    $podcast->update(['cover_image_path' => 'podcasts/new.png']);

    Storage::disk('public')->assertMissing([
        'podcasts/responsive/old-640.webp',
        'podcasts/responsive/old-1280.webp',
    ]);
    Storage::disk('public')->assertExists([
        'podcasts/responsive/new-640.webp',
        'podcasts/responsive/new-1280.webp',
    ]);

    $podcast->delete();

    Storage::disk('public')->assertMissing([
        'podcasts/responsive/new-640.webp',
        'podcasts/responsive/new-1280.webp',
    ]);
});

it('logs responsive generation failures without exposing media paths', function () {
    $logger = Double::for(LoggerInterface::class);
    $logger->expects('warning')
        ->with('Responsive project image generation failed. Run projects:generate-image-variants to retry.');
    $logger->expects('warning')
        ->with('Responsive post image generation failed. Run posts:generate-image-variants to retry.');
    $logger->expects('warning')
        ->with('Responsive podcast image generation failed. Run podcasts:generate-image-variants to retry.');
    Log::swap($logger);
    Storage::disk('public')->put('projects/private-project-name.png', 'not an image');
    Storage::disk('public')->put('posts/private-post-name.png', 'not an image');
    Storage::disk('public')->put('podcasts/private-podcast-name.png', 'not an image');
    $author = User::factory()->create();

    Project::query()->create([
        'title' => 'Project',
        'slug' => 'project',
        'description' => 'Description',
        'status' => PublishStatus::Draft,
        'featured_image_path' => 'projects/private-project-name.png',
    ]);
    Post::query()->create([
        'title' => 'Post',
        'slug' => 'post',
        'content' => 'Content',
        'user_id' => $author->id,
        'status' => PublishStatus::Draft,
        'featured_image_path' => 'posts/private-post-name.png',
    ]);
    Podcast::query()->create([
        'name' => 'Podcast',
        'slug' => 'podcast',
        'description' => 'Description',
        'cover_image_path' => 'podcasts/private-podcast-name.png',
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

it('keeps podcast and episode media when the podcast deletion rolls back', function () {
    Storage::disk('public')->put('podcasts/cover.png', 'cover');
    Storage::disk('public')->put('episodes/audio/episode.mp3', 'audio');

    $podcast = Podcast::query()->create([
        'name' => 'Podcast',
        'slug' => 'podcast',
        'description' => 'Description',
        'cover_image_path' => 'podcasts/cover.png',
    ]);
    $episode = Episode::query()->create([
        'podcast_id' => $podcast->id,
        'title' => 'Episode',
        'slug' => 'episode',
        'description' => 'Description',
        'audio_path' => 'episodes/audio/episode.mp3',
    ]);

    DB::beginTransaction();
    $podcast->delete();
    DB::rollBack();

    Storage::disk('public')->assertExists([
        'podcasts/cover.png',
        'episodes/audio/episode.mp3',
    ]);
});

it('keeps podcast and episode files when deletion is cancelled without an application transaction', function () {
    Storage::disk('public')->put('podcasts/kept.png', 'cover');
    Storage::disk('public')->put('episodes/audio/kept.mp3', 'audio');
    $podcast = Podcast::query()->create([
        'name' => 'Cancelled deletion',
        'description' => 'Preserve record-owned files on failure.',
        'cover_image_path' => 'podcasts/kept.png',
    ]);
    $episode = Episode::query()->create([
        'podcast_id' => $podcast->getKey(),
        'title' => 'Episode that must remain',
        'description' => 'Preserve episode files on failure.',
        'audio_path' => 'episodes/audio/kept.mp3',
    ]);
    Podcast::deleting(fn (): bool => false);

    $deleted = $podcast->delete();

    expect($deleted)->toBeFalse()
        ->and(Podcast::query()->whereKey($podcast->getKey())->exists())->toBeTrue()
        ->and(Episode::query()->whereKey($episode->getKey())->exists())->toBeTrue();
    Storage::disk('public')->assertExists(['podcasts/kept.png', 'episodes/audio/kept.mp3']);
});
