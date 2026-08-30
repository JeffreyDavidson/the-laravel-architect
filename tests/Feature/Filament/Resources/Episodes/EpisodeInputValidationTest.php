<?php

use App\Enums\PublishStatus;
use App\Filament\Resources\Episodes\Pages\CreateEpisode;
use App\Models\Episode;
use App\Models\Podcast;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

it('rejects episode text inputs longer than their database columns', function (string $field) {
    $this->actingAs(User::factory()->create(['is_admin' => true]));
    $podcast = Podcast::query()->create([
        'name' => 'Podcast name',
        'slug' => 'podcast-name',
        'description' => 'Podcast description',
    ]);

    livewire(CreateEpisode::class)
        ->fillForm([
            'podcast_id' => $podcast->id,
            'title' => 'Episode title',
            'slug' => 'episode-title',
            'description' => 'Episode description',
            'status' => PublishStatus::Draft,
            $field => str_repeat('a', 256),
        ])
        ->call('create')
        ->assertHasFormErrors([$field => 'max']);

    expect(Episode::query()->exists())->toBeFalse();
})->with([
    'title',
    'audio_url',
    'embed_url',
    'youtube_url',
    'guest_name',
    'guest_title',
    'guest_url',
]);
