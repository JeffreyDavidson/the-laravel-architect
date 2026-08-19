<?php

use App\Filament\Resources\Podcasts\Pages\CreatePodcast;
use App\Filament\Resources\Podcasts\Pages\EditPodcast;
use App\Models\Podcast;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $user = User::factory()->create(['is_admin' => true]);
    $this->actingAs($user);
});

it('rejects non-normalized podcast slugs when creating a podcast', function (string $slug) {
    livewire(CreatePodcast::class)
        ->fillForm([
            'name' => 'Podcast name',
            'slug' => $slug,
            'description' => 'Podcast description',
        ])
        ->call('create')
        ->assertHasFormErrors(['slug' => 'regex']);

    expect(Podcast::query()->exists())->toBeFalse();
})->with([
    'path traversal' => '../podcast-name',
    'spaces' => 'podcast name',
    'uppercase characters' => 'Podcast-Name',
    'leading hyphen' => '-podcast-name',
    'trailing hyphen' => 'podcast-name-',
    'repeated hyphens' => 'podcast--name',
]);

it('accepts a normalized podcast slug when creating a podcast', function () {
    livewire(CreatePodcast::class)
        ->fillForm([
            'name' => 'Podcast name',
            'slug' => 'podcast-name-2',
            'description' => 'Podcast description',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Podcast::query()->sole()->slug)->toBe('podcast-name-2');
});

it('rejects a non-normalized podcast slug when editing a podcast', function () {
    $podcast = Podcast::query()->create([
        'name' => 'Podcast name',
        'slug' => 'podcast-name',
        'description' => 'Podcast description',
    ]);

    livewire(EditPodcast::class, ['record' => $podcast->getRouteKey()])
        ->fillForm(['slug' => '../podcast-name'])
        ->call('save')
        ->assertHasFormErrors(['slug' => 'regex']);

    expect($podcast->refresh()->slug)->toBe('podcast-name');
});
