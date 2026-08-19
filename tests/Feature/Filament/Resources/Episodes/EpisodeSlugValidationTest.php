<?php

use App\Enums\PublishStatus;
use App\Filament\Resources\Episodes\Pages\CreateEpisode;
use App\Filament\Resources\Episodes\Pages\EditEpisode;
use App\Models\Episode;
use App\Models\Podcast;
use App\Models\User;
use Database\Seeders\ShieldSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(ShieldSeeder::class);

    $user = User::factory()->create(['is_admin' => true]);
    $user->assignRole('super_admin');
    $this->actingAs($user);

    $this->podcast = Podcast::query()->create([
        'name' => 'Podcast name',
        'slug' => 'podcast-name',
        'description' => 'Podcast description',
    ]);
});

it('rejects non-normalized episode slugs when creating an episode', function (string $slug) {
    livewire(CreateEpisode::class)
        ->fillForm([
            'podcast_id' => $this->podcast->id,
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
    livewire(CreateEpisode::class)
        ->fillForm([
            'podcast_id' => $this->podcast->id,
            'title' => 'Episode title',
            'slug' => 'episode-title-2',
            'description' => 'Episode description',
            'status' => PublishStatus::Draft,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Episode::query()->sole()->slug)->toBe('episode-title-2');
});

it('rejects a non-normalized episode slug when editing an episode', function () {
    $episode = Episode::query()->create([
        'podcast_id' => $this->podcast->id,
        'title' => 'Episode title',
        'slug' => 'episode-title',
        'description' => 'Episode description',
        'status' => PublishStatus::Draft,
    ]);

    livewire(EditEpisode::class, ['record' => $episode->getRouteKey()])
        ->fillForm(['slug' => '../episode-title'])
        ->call('save')
        ->assertHasFormErrors(['slug' => 'regex']);

    expect($episode->refresh()->slug)->toBe('episode-title');
});
