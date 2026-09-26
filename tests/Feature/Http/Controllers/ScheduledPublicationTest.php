<?php

use App\Enums\PublishStatus;
use App\Models\Episode;
use App\Models\Podcast;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

it('exposes scheduled posts across public surfaces exactly when due without changing their status', function () {
    $this->freezeSecond();
    $due = now()->addMinute();
    $post = Post::query()->create([
        'title' => 'Scheduled publication boundary',
        'content' => 'Public once its publication time arrives.',
        'user_id' => User::factory()->create()
            ->getKey(),
        'status' => PublishStatus::Scheduled,
        'published_at' => $due,
    ]);

    $this->get(route('blog.show', $post))
        ->assertNotFound();
    $this->get(route('blog.index'))
        ->assertDontSee($post->title);
    $this->get(route('rss'))
        ->assertDontSee($post->title);
    $this->get(route('sitemap'))
        ->assertDontSee(route('blog.show', $post));

    $this->travelTo($due);

    $this->get(route('blog.show', $post))
        ->assertOk();
    $this->get(route('blog.index'))
        ->assertOk()
        ->assertSee($post->title);
    $this->get(route('rss'))
        ->assertOk()
        ->assertSee($post->title);
    $this->get(route('sitemap'))
        ->assertOk()
        ->assertSee(route('blog.show', $post));

    expect($post->refresh()
        ->status)->toBe(PublishStatus::Scheduled);
});

it('exposes due scheduled episodes only while their podcast is active', function () {
    $this->freezeSecond();
    $due = now()->addMinute();
    $podcast = Podcast::query()->create([
        'name' => 'Scheduled podcast',
        'description' => 'Publication boundary coverage.',
        'is_active' => true,
    ]);
    $episode = Episode::query()->create([
        'podcast_id' => $podcast->getKey(),
        'title' => 'Scheduled episode boundary',
        'description' => 'Public once its publication time arrives.',
        'status' => PublishStatus::Scheduled,
        'published_at' => $due,
    ]);
    $url = route('podcast.episode', [$podcast, $episode]);

    $this->get($url)
        ->assertNotFound();
    $this->get(route('podcast.show', $podcast))
        ->assertDontSee($episode->title);
    $this->get(route('sitemap'))
        ->assertDontSee($url);

    $this->travelTo($due);

    $this->get($url)
        ->assertOk();
    $this->get(route('podcast.show', $podcast))
        ->assertOk()
        ->assertSee($episode->title);
    $this->get(route('sitemap'))
        ->assertOk()
        ->assertSee($url);

    expect($episode->refresh()
        ->status)->toBe(PublishStatus::Scheduled);

    $podcast->update(['is_active' => false]);

    $this->get($url)
        ->assertNotFound();
    $this->get(route('sitemap'))
        ->assertDontSee($url);
});
