<?php

use App\Enums\PublishStatus;
use App\Models\Post;
use App\Models\User;
use App\Queries\ArchiveQuery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

use function Pest\Laravel\travelTo;

pest()->use(RefreshDatabase::class);

covers(ArchiveQuery::class);

beforeEach(function () {
    config(['app.display_timezone' => 'America/New_York']);
    travelTo('2027-06-01 12:00:00');
    $author = User::factory()->create();

    foreach (['New Year Eve article' => '2027-01-01 03:00:00', 'New Year morning article' => '2027-01-01 06:00:00'] as $title => $publishedAt) {
        Post::query()->create([
            'title' => $title,
            'slug' => Str::slug($title),
            'content' => 'Published content.',
            'user_id' => $author->id,
            'status' => PublishStatus::Published,
            'published_at' => $publishedAt,
        ]);
    }
});

it('lists archive years in the display timezone', function () {
    $years = app(ArchiveQuery::class)->years();

    expect($years)
        ->toBe([2027, 2026]);
});

it('filters an archive year by its display timezone boundaries', function (int $year, string $title) {
    $items = app(ArchiveQuery::class)->get(year: $year);

    expect(array_column($items->items(), 'title'))
        ->toBe([$title]);
})->with([
    'evening of December 31 Eastern' => [2026, 'New Year Eve article'],
    'morning of January 1 Eastern' => [2027, 'New Year morning article'],
]);
