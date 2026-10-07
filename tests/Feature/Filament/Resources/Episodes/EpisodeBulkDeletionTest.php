<?php

use App\Filament\Resources\Episodes\Pages\ListEpisodes;
use App\Models\Episode;
use App\Models\User;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('public');
    actingAs(User::factory()->create(['is_admin' => true]));
});

it('permanently deletes trashed episodes and their native media through the table bulk action', function () {
    Storage::disk('public')->put('episodes/images/first-delete-test.png', 'image');
    Storage::disk('public')->put('episodes/images/second-delete-test.png', 'image');

    $episodes = Episode::factory()
        ->count(2)
        ->sequence(
            ['featured_image_path' => 'episodes/images/first-delete-test.png'],
            ['featured_image_path' => 'episodes/images/second-delete-test.png'],
        )
        ->create();

    $episodes->each->delete();

    livewire(ListEpisodes::class)
        ->filterTable('trashed', false)
        ->selectTableRecords($episodes)
        ->callAction(TestAction::make(ForceDeleteBulkAction::class)->table()
            ->bulk());

    expect(Episode::withTrashed()->whereKey($episodes->pluck('id'))
        ->count())->toBe(0);
    Storage::disk('public')->assertMissing([
        'episodes/images/first-delete-test.png',
        'episodes/images/second-delete-test.png',
    ]);
});
