<?php

use App\Filament\Resources\Episodes\Pages\EditEpisode;
use App\Models\Episode;
use App\Models\User;
use Filament\Actions\ForceDeleteAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('public');
    actingAs(User::factory()->create(['is_admin' => true]));
});

it('permanently deletes a trashed episode and its native media through the edit page', function () {
    Storage::disk('public')->put('episodes/images/delete-test.png', 'image');

    $episode = Episode::factory()->create(['featured_image_path' => 'episodes/images/delete-test.png']);

    $episode->delete();

    livewire(EditEpisode::class, ['record' => $episode->getRouteKey()])
        ->callAction(ForceDeleteAction::class);

    expect(Episode::withTrashed()->find($episode->id))->toBeNull();
    Storage::disk('public')->assertMissing([
        'episodes/images/delete-test.png',
    ]);
});
