<?php

use App\Enums\PublishStatus;
use App\Filament\Resources\Episodes\Pages\EditEpisode;
use App\Filament\Resources\NewsletterIssues\Pages\EditNewsletterIssue;
use App\Filament\Resources\Posts\Pages\CreatePost;
use App\Filament\Resources\Posts\Pages\EditPost;
use App\Models\Episode;
use App\Models\NewsletterIssue;
use App\Models\Podcast;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    config(['app.display_timezone' => 'America/New_York']);

    actingAs(User::factory()->create(['is_admin' => true]));
});

dataset('publishable edit pages', [
    'post' => [EditPost::class, fn (): Model => Post::query()->create([
        'title' => 'Scheduled post',
        'slug' => 'scheduled-post',
        'content' => 'Content.',
        'user_id' => auth()->id(),
        'status' => PublishStatus::Draft,
    ])],
    'episode' => [EditEpisode::class, function (): Model {
        $podcast = Podcast::query()->create(['name' => 'Show', 'slug' => 'show', 'description' => 'A show.']);

        return Episode::query()->create([
            'podcast_id' => $podcast->id,
            'title' => 'Scheduled episode',
            'slug' => 'scheduled-episode',
            'description' => 'Description.',
            'status' => PublishStatus::Draft,
        ]);
    }],
    'newsletter issue' => [EditNewsletterIssue::class, fn (): Model => NewsletterIssue::query()->create([
        'title' => 'Scheduled issue',
        'slug' => 'scheduled-issue',
        'content' => 'Content.',
        'status' => PublishStatus::Draft,
    ])],
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
