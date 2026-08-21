<?php

use App\Filament\Resources\Episodes\Pages\ListEpisodes;
use App\Models\Episode;
use App\Models\Podcast;
use App\Models\User;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('public');
    $this->actingAs(User::factory()->create(['is_admin' => true]));
});

it('deletes selected episodes and their native media through the table bulk action', function () {
    Storage::disk('public')->put('episodes/images/first-delete-test.png', 'image');
    Storage::disk('public')->put('episodes/audio/first-delete-test.mp3', 'audio');
    Storage::disk('public')->put('episodes/images/second-delete-test.png', 'image');
    Storage::disk('public')->put('episodes/audio/second-delete-test.mp3', 'audio');

    $podcast = Podcast::query()->create([
        'name' => 'Episode bulk delete coverage',
        'slug' => 'episode-bulk-delete-coverage',
        'description' => 'Podcast description',
    ]);
    $episodes = collect([
        Episode::query()->create([
            'podcast_id' => $podcast->id,
            'title' => 'First episode bulk delete coverage',
            'slug' => 'first-episode-bulk-delete-coverage',
            'description' => 'Episode description',
            'featured_image_path' => 'episodes/images/first-delete-test.png',
            'audio_path' => 'episodes/audio/first-delete-test.mp3',
        ]),
        Episode::query()->create([
            'podcast_id' => $podcast->id,
            'title' => 'Second episode bulk delete coverage',
            'slug' => 'second-episode-bulk-delete-coverage',
            'description' => 'Episode description',
            'featured_image_path' => 'episodes/images/second-delete-test.png',
            'audio_path' => 'episodes/audio/second-delete-test.mp3',
        ]),
    ]);

    livewire(ListEpisodes::class)
        ->selectTableRecords($episodes)
        ->callAction(TestAction::make(DeleteBulkAction::class)->table()->bulk());

    expect(Episode::query()->whereKey($episodes->pluck('id'))->count())->toBe(0);
    Storage::disk('public')->assertMissing([
        'episodes/images/first-delete-test.png',
        'episodes/audio/first-delete-test.mp3',
        'episodes/images/second-delete-test.png',
        'episodes/audio/second-delete-test.mp3',
    ]);
});
