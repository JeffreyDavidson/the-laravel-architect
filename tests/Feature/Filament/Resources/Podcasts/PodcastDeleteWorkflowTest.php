<?php

use App\Filament\Resources\Podcasts\Pages\EditPodcast;
use App\Models\Episode;
use App\Models\Podcast;
use App\Models\User;
use Filament\Actions\ForceDeleteAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    $user = User::factory()->create(['is_admin' => true]);
    actingAs($user);
});

it('permanently deletes a trashed podcast and its episodes through the authenticated resource', function () {
    Storage::fake('public');
    Storage::disk('public')->put('podcasts/delete-cover.jpg', 'cover');
    Storage::disk('public')->put('episodes/delete-image.jpg', 'image');

    $podcast = Podcast::factory()->create(['cover_image_path' => 'podcasts/delete-cover.jpg']);
    $episode = Episode::factory()
        ->for($podcast)
        ->create(['featured_image_path' => 'episodes/delete-image.jpg']);

    $podcast->delete();

    livewire(EditPodcast::class, ['record' => $podcast->getRouteKey()])
        ->callAction(ForceDeleteAction::class);

    expect(Podcast::withTrashed()->find($podcast->id))->toBeNull()
        ->and(Episode::withTrashed()->find($episode->id))
        ->toBeNull();

    Storage::disk('public')->assertMissing('podcasts/delete-cover.jpg');
    Storage::disk('public')->assertMissing('episodes/delete-image.jpg');
});
