<?php

use App\Enums\PublishStatus;
use App\Models\Episode;
use App\Models\Podcast;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\freezeSecond;
use function Pest\Laravel\get;
use function Pest\Laravel\travelTo;

pest()->use(RefreshDatabase::class);

it('exposes scheduled posts across public surfaces exactly when due without changing their status', function () {
    freezeSecond();
    $due = now()->addMinute();
    $post = Post::factory()
        ->scheduled()
        ->create([
            'title' => 'Scheduled publication boundary',
            'published_at' => $due,
        ]);

    get(route('blog.show', $post))
        ->assertNotFound();
    get(route('blog.index'))
        ->assertDontSee($post->title);
    get(route('rss'))
        ->assertDontSee($post->title);
    get(route('sitemap'))
        ->assertDontSee(route('blog.show', $post));

    travelTo($due);

    get(route('blog.show', $post))
        ->assertOk();
    get(route('blog.index'))
        ->assertOk()
        ->assertSee($post->title);
    get(route('rss'))
        ->assertOk()
        ->assertSee($post->title);
    get(route('sitemap'))
        ->assertOk()
        ->assertSee(route('blog.show', $post));

    expect($post->refresh()
        ->status)->toBe(PublishStatus::Scheduled);
});

it('exposes due scheduled episodes only while their podcast is active', function () {
    freezeSecond();
    $due = now()->addMinute();
    $podcast = Podcast::factory()->create();
    $episode = Episode::factory()
        ->for($podcast)
        ->scheduled()
        ->create([
            'title' => 'Scheduled episode boundary',
            'published_at' => $due,
        ]);
    $url = route('podcasts.episode', [$podcast, $episode]);

    get($url)
        ->assertNotFound();
    get(route('podcasts.show', $podcast))
        ->assertDontSee($episode->title);
    get(route('sitemap'))
        ->assertDontSee($url);

    travelTo($due);

    get($url)
        ->assertOk();
    get(route('podcasts.show', $podcast))
        ->assertOk()
        ->assertSee($episode->title);
    get(route('sitemap'))
        ->assertOk()
        ->assertSee($url);

    expect($episode->refresh()
        ->status)->toBe(PublishStatus::Scheduled);

    $podcast->update(['is_active' => false]);

    get($url)
        ->assertNotFound();
    get(route('sitemap'))
        ->assertDontSee($url);
});
