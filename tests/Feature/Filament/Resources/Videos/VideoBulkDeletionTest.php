<?php

use App\Filament\Resources\Videos\Pages\ListVideos;
use App\Models\User;
use App\Models\Video;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    actingAs(User::factory()->create(['is_admin' => true]));
});

it('deletes the selected videos through the table bulk action', function () {
    $selectedVideos = Video::factory()
        ->count(2)
        ->create();
    $remainingVideo = Video::factory()->create();

    livewire(ListVideos::class)
        ->selectTableRecords($selectedVideos)
        ->callAction(TestAction::make(DeleteBulkAction::class)->table()
            ->bulk());

    expect(Video::query()->whereKey($selectedVideos->pluck('id'))
        ->count())->toBe(0)
        ->and(Video::query()->find($remainingVideo->id))
        ->not->toBeNull();
});
