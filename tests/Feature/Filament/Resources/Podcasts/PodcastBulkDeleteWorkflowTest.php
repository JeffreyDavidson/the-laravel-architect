<?php

use App\Filament\Resources\Podcasts\Pages\ListPodcasts;
use App\Models\Podcast;
use App\Models\User;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->actingAs(User::factory()->create(['is_admin' => true]));
});

it('deletes multiple podcasts and their stored cover images through the table bulk action', function () {
    Storage::fake('public');
    Storage::disk('public')->put('podcasts/bulk-delete-one.jpg', 'cover one');
    Storage::disk('public')->put('podcasts/bulk-delete-two.jpg', 'cover two');

    $firstPodcast = Podcast::query()->create([
        'name' => 'First bulk delete podcast',
        'slug' => 'first-bulk-delete-podcast',
        'description' => 'Podcast description.',
        'cover_image_path' => 'podcasts/bulk-delete-one.jpg',
    ]);
    $secondPodcast = Podcast::query()->create([
        'name' => 'Second bulk delete podcast',
        'slug' => 'second-bulk-delete-podcast',
        'description' => 'Podcast description.',
        'cover_image_path' => 'podcasts/bulk-delete-two.jpg',
    ]);

    livewire(ListPodcasts::class)
        ->selectTableRecords([$firstPodcast, $secondPodcast])
        ->callAction(TestAction::make(DeleteBulkAction::class)->table()->bulk());

    expect(Podcast::query()->find($firstPodcast->id))->toBeNull()
        ->and(Podcast::query()->find($secondPodcast->id))->toBeNull();

    Storage::disk('public')->assertMissing('podcasts/bulk-delete-one.jpg');
    Storage::disk('public')->assertMissing('podcasts/bulk-delete-two.jpg');
});
