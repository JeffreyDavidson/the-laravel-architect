<?php

use App\Models\Episode;
use App\Models\Podcast;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

pest()->use(RefreshDatabase::class);

function relatedEpisode(string $slug): Episode
{
    return Episode::query()->create([
        'podcast_id' => Podcast::query()
            ->firstOrCreate(['slug' => 'show'], ['name' => 'Show', 'description' => 'A show.'])
            ->getKey(),
        'title' => "Episode {$slug}",
        'slug' => $slug,
        'description' => 'Description.',
    ]);
}

function relatingPost(): Post
{
    return Post::query()->create([
        'title' => 'Relating post',
        'slug' => 'relating-post',
        'content' => 'Content.',
        'user_id' => User::factory()
            ->create()
            ->getKey(),
    ]);
}

it('relates to every episode it is linked to', function () {
    $post = relatingPost();
    $first = relatedEpisode('first');
    $second = relatedEpisode('second');

    $post->episodes()
        ->attach([$first->getKey(), $second->getKey()]);

    $slugs = $post->episodes
        ->pluck('slug')
        ->sort()
        ->values()
        ->all();

    expect($slugs)
        ->toBe(['first', 'second']);
});

it('has no related episodes unless some are linked', function () {
    $post = relatingPost();

    expect($post->episodes)
        ->toBeEmpty();
});

it('does not link the same episode to a post twice', function () {
    $post = relatingPost();
    $episode = relatedEpisode('first');
    $post->episodes()
        ->attach($episode->getKey());

    $attachAgain = fn () => $post->episodes()
        ->attach($episode->getKey());

    expect($attachAgain)
        ->toThrow(QueryException::class);
});

it('leaves out a related episode while it is soft deleted', function () {
    $post = relatingPost();
    $episode = relatedEpisode('first');
    $post->episodes()
        ->attach($episode->getKey());

    $episode->delete();

    $post->refresh();

    expect($post->episodes)
        ->toBeEmpty();
});

it('keeps the post and drops the link when a related episode is permanently deleted', function () {
    $post = relatingPost();
    $episode = relatedEpisode('first');
    $post->episodes()
        ->attach($episode->getKey());

    $episode->forceDelete();

    $post->refresh();

    expect($post->episodes)
        ->toBeEmpty()
        ->and(Post::query()->whereKey($post->getKey())
            ->exists())
        ->toBeTrue();
});

it('drops its links when the post is permanently deleted', function () {
    $post = relatingPost();
    $episode = relatedEpisode('first');
    $post->episodes()
        ->attach($episode->getKey());

    $post->forceDelete();

    expect(Episode::query()->whereKey($episode->getKey())
        ->exists())
        ->toBeTrue()
        ->and(DB::table('episode_post')
            ->count())
        ->toBe(0);
});
