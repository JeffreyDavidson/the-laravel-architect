<?php

use App\Models\Episode;
use App\Models\Podcast;
use App\Models\Post;
use App\Models\Project;
use App\Models\Tag;
use App\Queries\RelatedProjectContentQuery;
use Carbon\CarbonInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\freezeSecond;

pest()->use(RefreshDatabase::class);

it('selects published tagged posts and episodes with reachable podcasts', function () {
    $tag = Tag::factory()->create(['name' => 'Architecture']);
    $project = Project::factory()
        ->published()
        ->create();
    $project->attachTag($tag);

    $post = Post::factory()
        ->published()
        ->create();
    $post->attachTag($tag);

    $episode = Episode::factory()
        ->published()
        ->create();
    $episode->attachTag($tag);

    $draftPost = Post::factory()->create();
    $draftPost->attachTag($tag);

    $inactiveEpisode = Episode::factory()
        ->for(Podcast::factory()->inactive())
        ->published()
        ->create();
    $inactiveEpisode->attachTag($tag);

    $related = app(RelatedProjectContentQuery::class)->get($project);

    expect($related['posts']->modelKeys())->toBe([$post->getKey()])
        ->and($related['episodes']->modelKeys())
        ->toBe([$episode->getKey()])
        ->and($related['posts']->sole()
            ->relationLoaded('category'))
        ->toBeTrue()
        ->and($related['episodes']->sole()
            ->relationLoaded('podcast'))
        ->toBeTrue();
});

function projectWithTaggedContent(): Project
{
    $tag = Tag::factory()->create(['name' => 'Architecture']);
    $project = Project::factory()
        ->published()
        ->create();
    $project->attachTag($tag);

    foreach (range(1, 3) as $daysAgo) {
        $post = Post::factory()
            ->published()
            ->create(['published_at' => now()->subDays($daysAgo)]);
        $post->attachTag($tag);

        $episode = Episode::factory()
            ->published()
            ->create(['published_at' => now()->subDays($daysAgo)]);
        $episode->attachTag($tag);
    }

    return $project;
}

it('returns empty related content when the project has no tags', function () {
    // Arrange
    $project = Project::factory()
        ->published()
        ->create();
    Post::factory()
        ->published()
        ->create()
        ->attachTag(Tag::factory()->create());

    // Act
    $related = app(RelatedProjectContentQuery::class)->get($project);

    // Assert
    expect($related['posts'])->toBeEmpty()
        ->and($related['episodes'])
        ->toBeEmpty();
});

it('returns empty related content when the limit is below one', function (int $limit) {
    // Arrange: matching content exists, so only the limit can empty the result
    $project = projectWithTaggedContent();

    // Act
    $related = app(RelatedProjectContentQuery::class)->get($project, $limit);

    // Assert
    expect($related['posts'])->toBeEmpty()
        ->and($related['episodes'])
        ->toBeEmpty();
})->with([
    'zero' => [0],
    'negative' => [-1],
]);

it('caps related posts and episodes at the limit, newest first', function () {
    // Arrange
    freezeSecond();
    $project = projectWithTaggedContent();
    $newestTwo = [
        now()
            ->subDay()
            ->toDateString(),
        now()
            ->subDays(2)
            ->toDateString(),
    ];

    // Act
    $related = app(RelatedProjectContentQuery::class)->get($project, 2);

    // Assert
    $postDates = $related['posts']->map(fn (Post $post): ?string => $post->published_at instanceof CarbonInterface ? $post->published_at->toDateString() : null);
    $episodeDates = $related['episodes']->map(fn (Episode $episode): ?string => $episode->published_at instanceof CarbonInterface ? $episode->published_at->toDateString() : null);
    expect($postDates->all())->toBe($newestTwo)
        ->and($episodeDates->all())
        ->toBe($newestTwo);
});
