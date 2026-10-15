<?php

use App\Models\Episode;
use App\Models\Podcast;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use JeffreyDavidson\CreatorKit\Enums\PublishStatus;

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

    $response = get(route('blog.show', $post));

    $response
        ->assertNotFound();
    $response = get(route('blog.index'));

    $response
        ->assertDontSee($post->title);
    $response = get(route('rss'));

    $response
        ->assertDontSee($post->title);
    $response = get(route('sitemap'));

    $response
        ->assertDontSee(route('blog.show', $post));

    travelTo($due);

    $response = get(route('blog.show', $post));

    $response
        ->assertOk();
    $response = get(route('blog.index'));

    $response
        ->assertOk()
        ->assertSee($post->title);
    $response = get(route('rss'));

    $response
        ->assertOk()
        ->assertSee($post->title);
    $response = get(route('sitemap'));

    $response
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

    $response = get($url);

    $response
        ->assertNotFound();
    $response = get(route('podcasts.show', $podcast));

    $response
        ->assertDontSee($episode->title);
    $response = get(route('sitemap'));

    $response
        ->assertDontSee($url);

    travelTo($due);

    $response = get($url);

    $response
        ->assertOk();
    $response = get(route('podcasts.show', $podcast));

    $response
        ->assertOk()
        ->assertSee($episode->title);
    $response = get(route('sitemap'));

    $response
        ->assertOk()
        ->assertSee($url);

    expect($episode->refresh()
        ->status)->toBe(PublishStatus::Scheduled);

    $podcast->update(['is_active' => false]);

    $response = get($url);

    $response
        ->assertNotFound();
    $response = get(route('sitemap'));

    $response
        ->assertDontSee($url);
});
