<?php

use App\Models\Episode;
use App\Models\Podcast;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use JeffreyDavidson\CreatorKit\Enums\PublishStatus;
use JeffreyDavidson\CreatorKit\Filament\Resources\Episodes\Pages\CreateEpisode;
use JeffreyDavidson\CreatorKit\Filament\Resources\Episodes\Pages\EditEpisode;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    $user = User::factory()->create(['is_admin' => true]);
    actingAs($user);
    Storage::fake('public');
});

it('creates an episode through the authenticated resource form', function () {
    $podcast = Podcast::factory()->create();

    livewire(CreateEpisode::class)
        ->fillForm([
            'podcast_id' => $podcast->id,
            'title' => 'Episode workflow coverage',
            'slug' => 'episode-workflow-coverage',
            'description' => 'Episode description',
            'transcript' => 'Episode transcript content.',
            'status' => PublishStatus::Draft,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Episode::query()->sole())
        ->title->toBe('Episode workflow coverage')
        ->slug->toBe('episode-workflow-coverage')
        ->podcast_id->toBe($podcast->id)
        ->transcript->toBe('Episode transcript content.')
        ->status->toBe(PublishStatus::Draft);
});

it('updates an episode through the authenticated resource form', function () {
    $episode = Episode::factory()->create();

    livewire(EditEpisode::class, ['record' => $episode->getRouteKey()])
        ->fillForm([
            'title' => 'Updated episode title',
            'slug' => 'updated-episode-title',
            'description' => 'Updated description',
            'transcript' => 'Updated transcript content.',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($episode->refresh())
        ->title->toBe('Updated episode title')
        ->slug->toBe('updated-episode-title')
        ->description->toBe('Updated description')
        ->transcript->toBe('Updated transcript content.')
        ->status->toBe(PublishStatus::Draft);
});
