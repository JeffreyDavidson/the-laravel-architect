<?php

use App\Models\Episode;
use App\Models\Podcast;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

function linkedEpisode(): Episode
{
    return Episode::query()->create([
        'podcast_id' => Podcast::query()
            ->create(['name' => 'Show', 'slug' => 'show', 'description' => 'A show.'])
            ->getKey(),
        'title' => 'Linked episode',
        'slug' => 'linked-episode',
        'description' => 'Description.',
    ]);
}

function postLinkedTo(?Episode $episode): Post
{
    return Post::query()->create([
        'title' => 'Linking post',
        'slug' => 'linking-post',
        'content' => 'Content.',
        'user_id' => User::factory()
            ->create()
            ->getKey(),
        'episode_id' => $episode?->getKey(),
    ]);
}

it('belongs to the related episode it links to', function () {
    $episode = linkedEpisode();

    $post = postLinkedTo($episode);

    expect($post->episode?->is($episode))
        ->toBeTrue();
});

it('has no related episode unless one is linked', function () {
    $post = postLinkedTo(null);

    expect($post->episode)
        ->toBeNull();
});

it('reports no related episode while the linked episode is soft deleted', function () {
    $episode = linkedEpisode();
    $post = postLinkedTo($episode);

    $episode->delete();

    expect($post->refresh()
        ->episode)
        ->toBeNull();
});

it('keeps the post and clears the link when the linked episode is permanently deleted', function () {
    $episode = linkedEpisode();
    $post = postLinkedTo($episode);

    $episode->forceDelete();

    expect($post->refresh())
        ->episode_id->toBeNull()
        ->and(Post::query()
            ->whereKey($post->getKey())
            ->exists())
        ->toBeTrue();
});
