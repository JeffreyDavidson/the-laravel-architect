<?php

use App\Enums\PublishStatus;
use App\Models\Category;
use App\Models\Episode;
use App\Models\Podcast;
use App\Models\Post;
use App\Models\Project;
use App\Models\Tag;
use App\Models\User;
use App\Queries\RelatedProjectContentQuery;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

it('selects published tagged posts and episodes with reachable podcasts', function () {
    $author = User::factory()->create();
    $category = Category::query()->create([
        'name' => 'Architecture',
        'slug' => 'architecture',
    ]);
    $podcast = Podcast::query()->create([
        'name' => 'Architecture Sessions',
        'slug' => 'architecture-sessions',
        'description' => 'Conversations about architecture.',
        'is_active' => true,
    ]);
    $tag = Tag::query()->create([
        'name' => ['en' => 'Architecture'],
        'slug' => ['en' => 'architecture'],
    ]);
    $project = Project::query()->create([
        'title' => 'Current project',
        'slug' => 'current-project',
        'description' => 'The current project.',
        'status' => PublishStatus::Published,
    ]);
    $project->attachTag($tag);

    $post = Post::query()->create([
        'title' => 'Related article',
        'slug' => 'related-article',
        'excerpt' => 'A related article.',
        'content' => 'Article content.',
        'category_id' => $category->getKey(),
        'user_id' => $author->getKey(),
        'status' => PublishStatus::Published,
        'published_at' => now()->subDay(),
    ]);
    $post->attachTag($tag);

    $episode = Episode::query()->create([
        'podcast_id' => $podcast->getKey(),
        'title' => 'Related episode',
        'slug' => 'related-episode',
        'description' => 'A related episode.',
        'status' => PublishStatus::Published,
        'published_at' => now()->subHours(2),
    ]);
    $episode->attachTag($tag);

    $draftPost = Post::query()->create([
        'title' => 'Draft article',
        'slug' => 'draft-article',
        'excerpt' => 'A draft article.',
        'content' => 'Draft content.',
        'category_id' => $category->getKey(),
        'user_id' => $author->getKey(),
        'status' => PublishStatus::Draft,
    ]);
    $draftPost->attachTag($tag);

    $inactivePodcast = Podcast::query()->create([
        'name' => 'Archived Sessions',
        'slug' => 'archived-sessions',
        'description' => 'An archived podcast.',
        'is_active' => false,
    ]);
    $inactiveEpisode = Episode::query()->create([
        'podcast_id' => $inactivePodcast->getKey(),
        'title' => 'Archived episode',
        'slug' => 'archived-episode',
        'description' => 'An archived episode.',
        'status' => PublishStatus::Published,
        'published_at' => now(),
    ]);
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

it('returns empty related content when the project has no tags or the limit is invalid', function () {
    $project = Project::query()->create([
        'title' => 'Untagged project',
        'slug' => 'untagged-project',
        'description' => 'A project without tags.',
        'status' => PublishStatus::Published,
    ]);

    $query = app(RelatedProjectContentQuery::class);

    expect($query->get($project)['posts'])->toBeEmpty()
        ->and($query->get($project)['episodes'])
        ->toBeEmpty()
        ->and($query->get($project, 0)['posts'])
        ->toBeEmpty()
        ->and($query->get($project, 0)['episodes'])
        ->toBeEmpty();
});
