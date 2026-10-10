<?php

use App\Models\Episode;
use App\Models\NewsletterIssue;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use JeffreyDavidson\CreatorKit\Enums\PublishStatus;
use JeffreyDavidson\CreatorKit\Filament\Resources\Episodes\Pages\EditEpisode;
use JeffreyDavidson\CreatorKit\Filament\Resources\NewsletterIssues\Pages\EditNewsletterIssue;
use JeffreyDavidson\CreatorKit\Filament\Resources\Posts\Pages\CreatePost;
use JeffreyDavidson\CreatorKit\Filament\Resources\Posts\Pages\EditPost;
use Tests\Support\PublishableFixtures;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    config(['app.display_timezone' => 'America/New_York']);

    actingAs(User::factory()->create(['is_admin' => true]));
});

dataset('publishable edit pages', [
    'post' => [EditPost::class, fn (): Post => Post::factory()->create()],
    'episode' => [EditEpisode::class, fn (): Episode => Episode::factory()->create()],
    'newsletter issue' => [EditNewsletterIssue::class, fn (): NewsletterIssue => NewsletterIssue::factory()->create()],
]);

dataset('eastern publish dates', [
    'daylight saving time' => ['2026-10-05 21:00:00', '2026-10-06 01:00:00'],
    'standard time' => ['2027-01-15 21:00:00', '2027-01-16 02:00:00'],
]);

it('stores a publish date entered in Eastern time as UTC', function (string $page, Model $record, string $entered, string $stored) {
    livewire($page, ['record' => $record->getRouteKey()])
        ->fillForm(['published_at' => $entered])
        ->call('save')
        ->assertHasNoFormErrors();

    assertDatabaseHas($record->getTable(), [
        'id' => $record->getKey(),
        'published_at' => $stored,
    ]);
})
    ->with('publishable edit pages')
    ->with('eastern publish dates');

it('shows a stored UTC publish date in Eastern time with a timezone hint', function (string $page, Model $record) {
    $record->forceFill(['published_at' => '2026-10-06 01:00:00'])
        ->save();

    livewire($page, ['record' => $record->getRouteKey()])
        ->assertSet('data.published_at', '2026-10-05 21:00:00')
        ->assertSee('Eastern Time (America/New_York)');
})->with('publishable edit pages');

it('stores the publish date of a new post entered in Eastern time as UTC', function (string $entered, string $stored) {
    livewire(CreatePost::class)
        ->fillForm([
            'title' => 'Evening post',
            'slug' => 'evening-post',
            'content' => 'Content.',
            'status' => PublishStatus::Draft,
            'published_at' => $entered,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    assertDatabaseHas('posts', [
        'slug' => 'evening-post',
        'published_at' => $stored,
    ]);
})->with('eastern publish dates');

it('requires a publish date for live and scheduled content so clearing it cannot take it offline', function (string $type, int $days, PublishStatus $status) {
    $publishedAt = now()
        ->addDays($days)
        ->startOfMinute();
    $record = PublishableFixtures::ready($type, ['published_at' => $publishedAt]);
    $record->publish();

    livewire(PublishableFixtures::editPage($type), ['record' => $record->getRouteKey()])
        ->fillForm(['published_at' => null])
        ->call('save')
        ->assertHasFormErrors(['published_at' => 'required']);

    expect($record->fresh())
        ->status->toBe($status)
        ->published_at->toEqual($publishedAt);
})->with([
    'published post' => ['post', -1, PublishStatus::Published],
    'scheduled post' => ['post', 1, PublishStatus::Scheduled],
    'published episode' => ['episode', -1, PublishStatus::Published],
    'published newsletter issue' => ['newsletter issue', -1, PublishStatus::Published],
]);

it('lets a draft clear its publish date', function (string $page, Model $record) {
    $record->forceFill(['published_at' => '2026-10-06 01:00:00'])
        ->save();

    livewire($page, ['record' => $record->getRouteKey()])
        ->fillForm(['published_at' => null])
        ->call('save')
        ->assertHasNoFormErrors();

    assertDatabaseHas($record->getTable(), [
        'id' => $record->getKey(),
        'published_at' => null,
    ]);
})->with('publishable edit pages');
