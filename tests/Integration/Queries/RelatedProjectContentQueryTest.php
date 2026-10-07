<?php

use App\Models\Episode;
use App\Models\Podcast;
use App\Models\Post;
use App\Models\Project;
use App\Models\Tag;
use App\Queries\RelatedProjectContentQuery;
use Illuminate\Foundation\Testing\RefreshDatabase;

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

it('returns empty related content when the project has no tags or the limit is invalid', function () {
    $project = Project::factory()
        ->published()
        ->create();

    $query = app(RelatedProjectContentQuery::class);

    expect($query->get($project)['posts'])->toBeEmpty()
        ->and($query->get($project)['episodes'])
        ->toBeEmpty()
        ->and($query->get($project, 0)['posts'])
        ->toBeEmpty()
        ->and($query->get($project, 0)['episodes'])
        ->toBeEmpty();
});
