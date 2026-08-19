<?php

use App\Enums\PublishStatus;
use App\Filament\Resources\Podcasts\Pages\EditPodcast;
use App\Models\Episode;
use App\Models\Podcast;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $user = User::factory()->create(['is_admin' => true]);
    $this->actingAs($user);
});

it('deletes a podcast and its episodes through the authenticated resource', function () {
    Storage::fake('public');
    Storage::disk('public')->put('podcasts/delete-cover.jpg', 'cover');
    Storage::disk('public')->put('episodes/delete-image.jpg', 'image');
    Storage::disk('public')->put('episodes/delete-audio.mp3', 'audio');

    $podcast = Podcast::query()->create([
        'name' => 'Podcast delete coverage',
        'slug' => 'podcast-delete-coverage',
        'description' => 'Podcast description.',
        'cover_image_path' => 'podcasts/delete-cover.jpg',
    ]);
    $episode = Episode::query()->create([
        'podcast_id' => $podcast->id,
        'title' => 'Episode delete coverage',
        'slug' => 'episode-delete-coverage',
        'description' => 'Episode description.',
        'featured_image_path' => 'episodes/delete-image.jpg',
        'audio_path' => 'episodes/delete-audio.mp3',
        'status' => PublishStatus::Draft,
    ]);

    livewire(EditPodcast::class, ['record' => $podcast->getRouteKey()])
        ->callAction(DeleteAction::class);

    expect(Podcast::query()->find($podcast->id))->toBeNull()
        ->and(Episode::query()->find($episode->id))->toBeNull();

    Storage::disk('public')->assertMissing('podcasts/delete-cover.jpg');
    Storage::disk('public')->assertMissing('episodes/delete-image.jpg');
    Storage::disk('public')->assertMissing('episodes/delete-audio.mp3');
});
