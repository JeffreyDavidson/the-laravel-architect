<?php

use App\Data\CalendarEntry;
use App\Enums\CalendarEntryType;
use App\Enums\PublishStatus;
use App\Filament\Resources\Episodes\EpisodeResource;
use App\Filament\Resources\Posts\PostResource;
use App\Models\Episode;
use App\Models\Post;
use App\Queries\EditorialCalendarQuery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

pest()->use(RefreshDatabase::class);

it('returns dated posts and episodes in the range followed by undated content', function () {
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

    expect($entries->all())->toEqual([
        new CalendarEntry(
            date: '2026-09-18',
            title: $post->title,
            type: CalendarEntryType::Post,
            status: PublishStatus::InReview,
            url: PostResource::getUrl('edit', ['record' => $post]),
        ),
        new CalendarEntry(
            date: '2026-09-22',
            title: $episode->title,
            type: CalendarEntryType::Episode,
            status: PublishStatus::Scheduled,
            url: EpisodeResource::getUrl('edit', ['record' => $episode]),
        ),
        new CalendarEntry(
            date: null,
            title: $undatedPost->title,
            type: CalendarEntryType::Post,
            status: PublishStatus::Draft,
            url: PostResource::getUrl('edit', ['record' => $undatedPost]),
        ),
    ]);
});

it('bounds the range and dates entries in the display timezone', function () {
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

    $datesByTitle = $entries->pluck('date', 'title')
        ->all();

    expect($datesByTitle)->toBe(['Evening post' => '2026-10-05']);
});
