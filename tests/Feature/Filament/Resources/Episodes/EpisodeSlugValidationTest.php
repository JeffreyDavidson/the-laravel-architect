<?php

use App\Filament\Resources\Episodes\Pages\CreateEpisode;
use App\Filament\Resources\Episodes\Pages\EditEpisode;
use App\Models\Episode;
use App\Models\Podcast;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use JeffreyDavidson\CreatorKit\Enums\PublishStatus;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    $user = User::factory()->create(['is_admin' => true]);
    actingAs($user);
});

it('rejects non-normalized episode slugs when creating an episode', function (string $slug) {
    $podcast = Podcast::factory()->create();

    livewire(CreateEpisode::class)
        ->fillForm([
            'podcast_id' => $podcast->id,
            'title' => 'Episode title',
            'slug' => $slug,
            'description' => 'Episode description',
            'status' => PublishStatus::Draft,
        ])
        ->call('create')
        ->assertHasFormErrors(['slug' => 'regex']);

    expect(Episode::query()->exists())->toBeFalse();
})->with([
    'path traversal' => '../episode-title',
    'spaces' => 'episode title',
    'uppercase characters' => 'Episode-Title',
    'leading hyphen' => '-episode-title',
    'trailing hyphen' => 'episode-title-',
    'repeated hyphens' => 'episode--title',
]);

it('accepts a normalized episode slug when creating an episode', function () {
    $podcast = Podcast::factory()->create();

    livewire(CreateEpisode::class)
        ->fillForm([
            'podcast_id' => $podcast->id,
            'title' => 'Episode title',
            'slug' => 'episode-title-2',
            'description' => 'Episode description',
            'status' => PublishStatus::Draft,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Episode::query()->sole()
        ->slug)->toBe('episode-title-2');
});

it('rejects a non-normalized episode slug when editing an episode', function () {
    $episode = Episode::factory()->create(['slug' => 'episode-title']);

    livewire(EditEpisode::class, ['record' => $episode->getRouteKey()])
        ->fillForm(['slug' => '../episode-title'])
        ->call('save')
        ->assertHasFormErrors(['slug' => 'regex']);

    expect($episode->refresh()
        ->slug)->toBe('episode-title');
});

it('preserves an existing episode slug when the title changes', function () {
    $episode = Episode::factory()->create(['slug' => 'curated-episode-slug']);

    livewire(EditEpisode::class, ['record' => $episode->getRouteKey()])
        ->fillForm(['title' => 'Updated episode title', 'slug' => 'curated-episode-slug'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($episode->refresh()
        ->slug)->toBe('curated-episode-slug');
});
