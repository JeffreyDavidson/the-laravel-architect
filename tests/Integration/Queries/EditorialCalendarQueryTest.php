<?php

use App\Models\Episode;
use App\Models\Post;
use App\Queries\EditorialCalendarQuery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

pest()->use(RefreshDatabase::class);

it('returns posts then episodes dated in the range or undated, each in publication order', function () {
    $undatedPost = Post::factory()->create(['published_at' => null]);
    $episode = Episode::factory()
        ->scheduled()
        ->create(['published_at' => '2026-09-22 09:00:00']);
    $post = Post::factory()
        ->inReview()
        ->create(['published_at' => '2026-09-18 09:00:00']);
    Post::factory()
        ->published()
        ->create(['published_at' => '2026-07-18 09:00:00']);

    $entries = app(EditorialCalendarQuery::class)->get(Carbon::parse('2026-08-30'), Carbon::parse('2026-10-03'));

    $records = $entries
        ->map(fn (Post|Episode $record): string => $record::class.':'.$record->id)
        ->all();

    expect($records)->toBe([
        Post::class.':'.$undatedPost->id,
        Post::class.':'.$post->id,
        Episode::class.':'.$episode->id,
    ]);
});

it('bounds the range by days in the display timezone', function () {
    config(['app.display_timezone' => 'America/New_York']);
    Post::factory()->create([
        'title' => 'Evening post',
        'published_at' => '2026-10-06 01:00:00',
    ]);
    Post::factory()->create([
        'title' => 'Next morning post',
        'published_at' => '2026-10-06 04:00:00',
    ]);

    $entries = app(EditorialCalendarQuery::class)->get(
        Carbon::parse('2026-10-05', 'America/New_York'),
        Carbon::parse('2026-10-05', 'America/New_York'),
    );

    $titles = $entries->pluck('title')
        ->all();

    expect($titles)->toBe(['Evening post']);
});
