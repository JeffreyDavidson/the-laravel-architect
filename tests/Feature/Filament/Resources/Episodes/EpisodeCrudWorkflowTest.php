<?php

use App\Enums\PublishStatus;
use App\Filament\Resources\Episodes\Pages\CreateEpisode;
use App\Filament\Resources\Episodes\Pages\EditEpisode;
use App\Models\Episode;
use App\Models\Podcast;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $user = User::factory()->create(['is_admin' => true]);
    $this->actingAs($user);
    $this->podcast = Podcast::query()->create([
        'name' => 'Workflow podcast',
        'slug' => 'workflow-podcast',
        'description' => 'Podcast description',
    ]);
});

it('creates an episode through the authenticated resource form', function () {
    livewire(CreateEpisode::class)
        ->fillForm([
            'podcast_id' => $this->podcast->id,
            'title' => 'Episode workflow coverage',
            'slug' => 'episode-workflow-coverage',
            'description' => 'Episode description',
            'status' => PublishStatus::Draft,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Episode::query()->sole())
        ->title->toBe('Episode workflow coverage')
        ->slug->toBe('episode-workflow-coverage')
        ->podcast_id->toBe($this->podcast->id)
        ->status->toBe(PublishStatus::Draft);
});

it('updates an episode through the authenticated resource form', function () {
    $episode = Episode::query()->create([
        'podcast_id' => $this->podcast->id,
        'title' => 'Original episode title',
        'slug' => 'original-episode-title',
        'description' => 'Original description',
        'status' => PublishStatus::Draft,
    ]);

    livewire(EditEpisode::class, ['record' => $episode->getRouteKey()])
        ->fillForm([
            'title' => 'Updated episode title',
            'slug' => 'updated-episode-title',
            'description' => 'Updated description',
            'status' => PublishStatus::Published,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($episode->refresh())
        ->title->toBe('Updated episode title')
        ->slug->toBe('updated-episode-title')
        ->description->toBe('Updated description')
        ->status->toBe(PublishStatus::Published);
});
