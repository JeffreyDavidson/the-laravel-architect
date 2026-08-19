<?php

use App\Filament\Resources\Episodes\Pages\EditEpisode;
use App\Models\Episode;
use App\Models\Podcast;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('public');
    $this->actingAs(User::factory()->create(['is_admin' => true]));
});

it('deletes an episode and its native media through the edit page', function () {
    Storage::disk('public')->put('episodes/images/delete-test.png', 'image');
    Storage::disk('public')->put('episodes/audio/delete-test.mp3', 'audio');

    $podcast = Podcast::query()->create([
        'name' => 'Episode delete coverage',
        'slug' => 'episode-delete-coverage',
        'description' => 'Podcast description',
    ]);
    $episode = Episode::query()->create([
        'podcast_id' => $podcast->id,
        'title' => 'Episode delete coverage',
        'slug' => 'episode-delete-coverage',
        'description' => 'Episode description',
        'featured_image_path' => 'episodes/images/delete-test.png',
        'audio_path' => 'episodes/audio/delete-test.mp3',
    ]);

    livewire(EditEpisode::class, ['record' => $episode->getRouteKey()])
        ->callAction(DeleteAction::class);

    expect(Episode::query()->find($episode->id))->toBeNull();
    Storage::disk('public')->assertMissing([
        'episodes/images/delete-test.png',
        'episodes/audio/delete-test.mp3',
    ]);
});
