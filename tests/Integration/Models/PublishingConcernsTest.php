<?php

use App\Enums\PublishStatus;
use App\Models\Episode;
use App\Models\Post;
use App\Models\Project;
use App\Models\Video;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\freezeSecond;

pest()->use(RefreshDatabase::class);

it('keeps date-based visibility consistent between model checks and database scopes', function (PublishStatus $status, ?int $seconds, bool $visible, bool $scheduled) {
    freezeSecond();
    $publishedAt = $seconds === null ? null : now()->addSeconds($seconds);
    $post = Post::factory()->create([
        'status' => $status,
        'published_at' => $publishedAt,
    ]);
    $episode = Episode::factory()->create([
        'status' => $status,
        'published_at' => $publishedAt,
    ]);

    expect($post->isPublished())->toBe($visible)
        ->and(Post::published()->whereKey($post->getKey())
            ->exists())
        ->toBe($visible)
        ->and($episode->isPublished())
        ->toBe($visible)
        ->and(Episode::published()->whereKey($episode->getKey())
            ->exists())
        ->toBe($visible)
        ->and(Post::query()->unpublished()
            ->whereKey($post->getKey())
            ->exists())
        ->toBe(! $visible)
        ->and(Episode::query()->unpublished()
            ->whereKey($episode->getKey())
            ->exists())
        ->toBe(! $visible)
        ->and(Post::query()->scheduled()
            ->whereKey($post->getKey())
            ->exists())
        ->toBe($scheduled)
        ->and(Episode::query()->scheduled()
            ->whereKey($episode->getKey())
            ->exists())
        ->toBe($scheduled);
})->with([
    'overdue scheduled' => [PublishStatus::Scheduled, -1, true, false],
    'exactly due scheduled' => [PublishStatus::Scheduled, 0, true, false],
    'future scheduled' => [PublishStatus::Scheduled, 1, false, true],
    'scheduled without a date' => [PublishStatus::Scheduled, null, false, false],
    'past published' => [PublishStatus::Published, -1, true, false],
    'future published' => [PublishStatus::Published, 1, false, true],
    'published without a date' => [PublishStatus::Published, null, false, false],
    'past draft' => [PublishStatus::Draft, -1, false, false],
]);

it('does not make a project public merely because it has a scheduled status', function () {
    expect(new Project(['status' => PublishStatus::Scheduled])->isPublished())->toBeFalse();
});

it('shares publishing behavior with projects despite their project status enum', function () {
    $project = Project::factory()
        ->published()
        ->create();

    expect($project->isPublished())->toBeTrue()
        ->and(Project::published()->pluck('id')
            ->all())
        ->toBe([$project->id]);
});

it('shares featured behavior across projects and videos', function () {
    $project = Project::factory()
        ->featured()
        ->create();
    $video = Video::factory()->create(['is_featured' => true]);

    expect($project->isFeatured())->toBeTrue()
        ->and($video->isFeatured())
        ->toBeTrue()
        ->and(Project::featured()->pluck('id')
            ->all())
        ->toBe([$project->id])
        ->and(Video::featured()->pluck('id')
            ->all())
        ->toBe([$video->id]);
});

it('shares featured image URL behavior across models', function () {
    Storage::fake('public');

    $post = new Post(['featured_image_path' => 'posts/image.png']);
    $project = new Project(['featured_image_path' => 'projects/image.png']);
    $episode = new Episode(['featured_image_path' => 'episodes/image.png']);

    expect($post->featured_image_url)->toBe(Storage::disk('public')->url('posts/image.png'))
        ->and($project->featured_image_url)
        ->toBe(Storage::disk('public')->url('projects/image.png'))
        ->and($episode->featured_image_url)
        ->toBe(Storage::disk('public')->url('episodes/image.png'));
});

it('preserves publishing behavior for posts', function () {
    $post = Post::factory()
        ->published()
        ->create();

    expect($post->isPublished())->toBeTrue()
        ->and(Post::published()->pluck('id')
            ->all())
        ->toBe([$post->id]);
});

it('shares date-only publishing behavior with videos', function () {
    $video = Video::factory()->create();

    expect($video->isPublished())->toBeTrue()
        ->and(Video::published()->pluck('id')
            ->all())
        ->toBe([$video->id]);
});
