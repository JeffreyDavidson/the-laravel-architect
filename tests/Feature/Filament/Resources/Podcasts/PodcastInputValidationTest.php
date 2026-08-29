<?php

use App\Filament\Resources\Podcasts\Pages\CreatePodcast;
use App\Models\Podcast;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

it('rejects podcast text inputs longer than their database columns', function (string $field) {
    $this->actingAs(User::factory()->create(['is_admin' => true]));

    livewire(CreatePodcast::class)
        ->fillForm([
            'name' => 'Podcast name',
            'slug' => 'podcast-name',
            'description' => 'Podcast description',
            $field => str_repeat('a', 256),
        ])
        ->call('create')
        ->assertHasFormErrors([$field => 'max']);

    expect(Podcast::query()->exists())->toBeFalse();
})->with([
    'name',
    'apple_url',
    'spotify_url',
    'rss_url',
    'youtube_url',
]);
