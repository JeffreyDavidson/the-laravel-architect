<?php

use App\Filament\Resources\Podcasts\Pages\ListPodcasts;
use App\Models\Podcast;
use App\Models\User;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    actingAs(User::factory()->create(['is_admin' => true]));
});

it('permanently deletes trashed podcasts and their stored cover images through the table bulk action', function () {
    Storage::fake('public');
    Storage::disk('public')->put('podcasts/bulk-delete-one.jpg', 'cover one');
    Storage::disk('public')->put('podcasts/bulk-delete-two.jpg', 'cover two');

    $firstPodcast = Podcast::factory()->create(['cover_image_path' => 'podcasts/bulk-delete-one.jpg']);
    $secondPodcast = Podcast::factory()->create(['cover_image_path' => 'podcasts/bulk-delete-two.jpg']);

    collect([$firstPodcast, $secondPodcast])->each->delete();

    livewire(ListPodcasts::class)
        ->filterTable('trashed', false)
        ->selectTableRecords([$firstPodcast, $secondPodcast])
        ->callAction(TestAction::make(ForceDeleteBulkAction::class)->table()
            ->bulk());

    expect(Podcast::withTrashed()->find($firstPodcast->id))->toBeNull()
        ->and(Podcast::withTrashed()->find($secondPodcast->id))
        ->toBeNull();

    Storage::disk('public')->assertMissing('podcasts/bulk-delete-one.jpg');
    Storage::disk('public')->assertMissing('podcasts/bulk-delete-two.jpg');
});
