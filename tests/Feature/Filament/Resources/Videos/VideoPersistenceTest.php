<?php

use App\Filament\Resources\Videos\Pages\EditVideo;
use App\Models\User;
use App\Models\Video;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => actingAs(User::factory()->create(['is_admin' => true])));

it('features a video on the homepage from the edit form', function () {
    // Arrange
    $video = Video::factory()->create(['is_featured' => false]);

    // Act
    $component = livewire(EditVideo::class, ['record' => $video->getRouteKey()])
        ->fillForm(['is_featured' => true])
        ->call('save');

    // Assert
    $component->assertHasNoFormErrors();
    expect($video->refresh())
        ->is_featured->toBeTrue();
});

it('leaves the fields synced from YouTube unchanged when the edit form is saved', function () {
    // Arrange
    $video = Video::factory()->create([
        'title' => 'Synced title',
        'youtube_id' => 'synced12345',
        'view_count' => 1200,
    ]);

    // Act
    $component = livewire(EditVideo::class, ['record' => $video->getRouteKey()])
        ->fillForm([
            'title' => 'Edited title',
            'youtube_id' => 'edited67890',
            'view_count' => '99',
            'is_featured' => true,
        ])
        ->call('save');

    // Assert
    $component->assertHasNoFormErrors();
    expect($video->refresh())
        ->title->toBe('Synced title')
        ->youtube_id->toBe('synced12345')
        ->view_count->toBe(1200)
        ->is_featured->toBeTrue();
});
