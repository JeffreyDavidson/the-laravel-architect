<?php

use App\Models\Episode;
use App\Models\Podcast;
use App\Models\Post;
use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use JMac\Testing\Double;
use Psr\Log\LoggerInterface;
use RalphJSmit\Laravel\SEO\Models\SEO;

use function Pest\Laravel\assertModelExists;
use function Pest\Laravel\assertModelMissing;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('public');
});

it('deletes owned episode metadata and tag links with its podcast', function () {
    $podcast = Podcast::factory()->create();
    $episode = Episode::factory()
        ->for($podcast)
        ->create();
    $episode->attachTag('Architecture');
    $seoIds = [$podcast->seo()
        ->sole()
        ->getKey(), $episode->seo()
        ->sole()
        ->getKey()];

    $podcast->forceDelete();

    assertModelMissing($episode);
    expect(DB::table('taggables')->where('taggable_type', $episode->getMorphClass())
        ->where('taggable_id', $episode->id)
        ->exists())->toBeFalse()
        ->and(SEO::query()->whereKey($seoIds)
            ->exists())
        ->toBeFalse();
});

it('preserves owned content when podcast deletion rolls back', function () {
    $podcast = Podcast::factory()->create();
    $episode = Episode::factory()
        ->for($podcast)
        ->create();
    $episode->attachTag('Architecture');
    $seoIds = [$podcast->seo()
        ->sole()
        ->getKey(), $episode->seo()
        ->sole()
        ->getKey()];

    DB::beginTransaction();
    $podcast->forceDelete();
    DB::rollBack();

    assertModelExists($episode);
    expect($episode->tags()
        ->count())->toBe(1)
        ->and(SEO::query()->whereKey($seoIds)
            ->count())
        ->toBe(2);
});

it('deletes the previous file when native media is replaced', function () {
    Storage::disk('public')->put('projects/old.png', 'old');
    Storage::disk('public')->put('projects/new.png', 'new');

    $project = Project::factory()->create(['featured_image_path' => 'projects/old.png']);

    $project->update(['featured_image_path' => 'projects/new.png']);

    Storage::disk('public')->assertMissing('projects/old.png');
    Storage::disk('public')->assertExists('projects/new.png');
});

it('deletes the previous original when post, podcast or episode media is replaced', function (string $model, string $column, string $directory) {
    /** @var class-string<Post|Podcast|Episode> $model */
    Storage::disk('public')->put("{$directory}/old.png", 'old');
    Storage::disk('public')->put("{$directory}/new.png", 'new');
    $record = $model::factory()
        ->create([$column => "{$directory}/old.png"]);

    $record->update([$column => "{$directory}/new.png"]);

    Storage::disk('public')->assertMissing("{$directory}/old.png");
    Storage::disk('public')->assertExists("{$directory}/new.png");
})->with([
    'post' => [Post::class, 'featured_image_path', 'posts'],
    'podcast' => [Podcast::class, 'cover_image_path', 'podcasts'],
    'episode' => [Episode::class, 'featured_image_path', 'episodes/images'],
]);

it('keeps native media when unrelated attributes change', function () {
    Storage::disk('public')->put('projects/image.png', 'image');

    $project = Project::factory()->create(['featured_image_path' => 'projects/image.png']);

    $project->update(['title' => 'Renamed Project']);

    Storage::disk('public')->assertExists('projects/image.png');
});

it('keeps replaced native media when the transaction rolls back', function () {
    Storage::disk('public')->put('projects/old.png', 'old');
    Storage::disk('public')->put('projects/new.png', 'new');

    $project = Project::factory()->create(['featured_image_path' => 'projects/old.png']);

    DB::beginTransaction();
    $project->update(['featured_image_path' => 'projects/new.png']);
    DB::rollBack();

    Storage::disk('public')->assertExists('projects/old.png');
    Storage::disk('public')->assertExists('projects/new.png');
});

it('preserves original images and responsive variants when replacement rolls back', function () {
    $image = UploadedFile::fake()->image('project.png', 1280, 8)
        ->getContent();
    Storage::disk('public')->put('projects/original.png', $image);
    Storage::disk('public')->put('projects/replacement.png', $image);
    $project = Project::factory()->create(['featured_image_path' => 'projects/original.png']);

    DB::beginTransaction();
    $project->update(['featured_image_path' => 'projects/replacement.png']);
    DB::rollBack();

    expect($project->refresh()
        ->featured_image_path)->toBe('projects/original.png');
    Storage::disk('public')->assertExists([
        'projects/original.png',
        'projects/responsive/original-640.webp',
        'projects/responsive/original-1280.webp',
    ]);
});

it('generates responsive variants for new media only after the transaction commits', function () {
    $image = UploadedFile::fake()->image('project.png', 1280, 8)
        ->getContent();
    Storage::disk('public')->put('projects/kept.png', $image);
    Storage::disk('public')->put('projects/discarded.png', $image);

    DB::beginTransaction();
    Project::factory()->create(['featured_image_path' => 'projects/discarded.png']);
    DB::rollBack();
    DB::beginTransaction();
    Project::factory()->create(['featured_image_path' => 'projects/kept.png']);
    DB::commit();

    Storage::disk('public')->assertMissing([
        'projects/responsive/discarded-640.webp',
        'projects/responsive/discarded-1280.webp',
    ]);
    Storage::disk('public')->assertExists([
        'projects/responsive/kept-640.webp',
        'projects/responsive/kept-1280.webp',
    ]);
});

it('preserves cached OG images when post deletion rolls back', function () {
    Storage::fake('local');
    $post = Post::factory()->create();
    $path = "og-images/{$post->id}/cached.png";
    Storage::disk('local')->put($path, 'cached image');

    DB::beginTransaction();
    $post->forceDelete();
    DB::rollBack();

    expect(Post::query()->whereKey($post->getKey())
        ->exists())->toBeTrue();
    Storage::disk('local')->assertExists($path);
});

it('deletes replaced native media after the transaction commits', function () {
    Storage::disk('public')->put('projects/old.png', 'old');
    Storage::disk('public')->put('projects/new.png', 'new');

    $project = Project::factory()->create(['featured_image_path' => 'projects/old.png']);

    DB::beginTransaction();
    $project->update(['featured_image_path' => 'projects/new.png']);
    DB::commit();

    Storage::disk('public')->assertMissing('projects/old.png');
    Storage::disk('public')->assertExists('projects/new.png');
});

it('deletes native media with its record', function () {
    Storage::disk('public')->put('projects/image.png', 'image');

    $project = Project::factory()->create(['featured_image_path' => 'projects/image.png']);

    $project->forceDelete();

    Storage::disk('public')->assertMissing('projects/image.png');
});

it('keeps responsive project image variants in sync with the original image', function () {
    $image = UploadedFile::fake()->image('project.png', 1280, 8)
        ->getContent();
    Storage::disk('public')->put('projects/old.png', $image);
    Storage::disk('public')->put('projects/new.png', $image);

    $project = Project::factory()->create(['featured_image_path' => 'projects/old.png']);

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

    $project->forceDelete();

    Storage::disk('public')->assertMissing([
        'projects/responsive/new-640.webp',
        'projects/responsive/new-1280.webp',
    ]);
});

it('keeps responsive post image variants in sync with the original image', function () {
    $image = UploadedFile::fake()->image('post.png', 1280, 8)
        ->getContent();
    Storage::disk('public')->put('posts/old.png', $image);
    Storage::disk('public')->put('posts/new.png', $image);

    $post = Post::factory()->create(['featured_image_path' => 'posts/old.png']);

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

    $post->forceDelete();

    Storage::disk('public')->assertMissing([
        'posts/responsive/new-640.webp',
        'posts/responsive/new-1280.webp',
    ]);
});

it('keeps responsive podcast cover variants in sync with the original image', function () {
    $image = UploadedFile::fake()->image('podcast.png', 1280, 8)
        ->getContent();
    Storage::disk('public')->put('podcasts/old.png', $image);
    Storage::disk('public')->put('podcasts/new.png', $image);

    $podcast = Podcast::factory()->create(['cover_image_path' => 'podcasts/old.png']);

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

    $podcast->forceDelete();

    Storage::disk('public')->assertMissing([
        'podcasts/responsive/new-640.webp',
        'podcasts/responsive/new-1280.webp',
    ]);
});

it('logs responsive generation failures without exposing media paths', function () {
    $logger = Double::for(LoggerInterface::class);
    $logger->expects('warning')
        ->with('Responsive project image generation failed. Run media:repair-responsive-images to retry.');
    $logger->expects('warning')
        ->with('Responsive post image generation failed. Run media:repair-responsive-images to retry.');
    $logger->expects('warning')
        ->with('Responsive podcast image generation failed. Run media:repair-responsive-images to retry.');
    Log::swap($logger);
    Storage::disk('public')->put('projects/private-project-name.png', 'not an image');
    Storage::disk('public')->put('posts/private-post-name.png', 'not an image');
    Storage::disk('public')->put('podcasts/private-podcast-name.png', 'not an image');

    Project::factory()->create(['featured_image_path' => 'projects/private-project-name.png']);
    Post::factory()->create(['featured_image_path' => 'posts/private-post-name.png']);
    Podcast::factory()->create(['cover_image_path' => 'podcasts/private-podcast-name.png']);

});

it('deletes episode media when its podcast is deleted', function () {
    Storage::disk('public')->put('podcasts/cover.png', 'cover');
    Storage::disk('public')->put('episodes/images/episode.png', 'image');

    $podcast = Podcast::factory()->create(['cover_image_path' => 'podcasts/cover.png']);
    Episode::factory()
        ->for($podcast)
        ->create(['featured_image_path' => 'episodes/images/episode.png']);

    $podcast->forceDelete();

    Storage::disk('public')->assertMissing([
        'podcasts/cover.png',
        'episodes/images/episode.png',
    ]);
});

it('keeps podcast and episode media when the podcast deletion rolls back', function () {
    Storage::disk('public')->put('podcasts/cover.png', 'cover');
    Storage::disk('public')->put('episodes/images/episode.png', 'image');

    $podcast = Podcast::factory()->create(['cover_image_path' => 'podcasts/cover.png']);
    $episode = Episode::factory()
        ->for($podcast)
        ->create(['featured_image_path' => 'episodes/images/episode.png']);

    DB::beginTransaction();
    $podcast->forceDelete();
    DB::rollBack();

    Storage::disk('public')->assertExists([
        'podcasts/cover.png',
        'episodes/images/episode.png',
    ]);
});

it('keeps podcast and episode files when deletion is cancelled without an application transaction', function () {
    Storage::disk('public')->put('podcasts/kept.png', 'cover');
    Storage::disk('public')->put('episodes/images/kept.png', 'image');
    $podcast = Podcast::factory()->create(['cover_image_path' => 'podcasts/kept.png']);
    $episode = Episode::factory()
        ->for($podcast)
        ->create(['featured_image_path' => 'episodes/images/kept.png']);
    Podcast::deleting(fn (): bool => false);

    $deleted = $podcast->delete();

    expect($deleted)->toBeFalse()
        ->and(Podcast::query()->whereKey($podcast->getKey())
            ->exists())
        ->toBeTrue()
        ->and(Episode::query()->whereKey($episode->getKey())
            ->exists())
        ->toBeTrue();
    Storage::disk('public')->assertExists(['podcasts/kept.png', 'episodes/images/kept.png']);
});
