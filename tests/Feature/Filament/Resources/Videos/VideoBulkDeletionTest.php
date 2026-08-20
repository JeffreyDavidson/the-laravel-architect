<?php

use App\Filament\Resources\Videos\Pages\ListVideos;
use App\Models\User;
use App\Models\Video;
use Filament\Actions\DeleteBulkAction;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->actingAs(User::factory()->create(['is_admin' => true]));
});

it('deletes the selected videos through the table bulk action', function () {
    $selectedVideos = collect([
        Video::query()->create([
            'youtube_id' => 'video-bulk-delete-one',
            'title' => 'First video bulk delete coverage',
            'slug' => 'first-video-bulk-delete-coverage',
        ]),
        Video::query()->create([
            'youtube_id' => 'video-bulk-delete-two',
            'title' => 'Second video bulk delete coverage',
            'slug' => 'second-video-bulk-delete-coverage',
        ]),
    ]);
    $remainingVideo = Video::query()->create([
        'youtube_id' => 'video-bulk-delete-remaining',
        'title' => 'Remaining video bulk delete coverage',
        'slug' => 'remaining-video-bulk-delete-coverage',
    ]);

    livewire(ListVideos::class)
        ->callTableBulkAction(DeleteBulkAction::class, $selectedVideos);

    expect(Video::query()->whereKey($selectedVideos->pluck('id'))->count())->toBe(0)
        ->and(Video::query()->find($remainingVideo->id))->not->toBeNull();
});
