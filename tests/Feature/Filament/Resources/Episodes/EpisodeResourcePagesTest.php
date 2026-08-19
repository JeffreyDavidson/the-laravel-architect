<?php

use App\Filament\Resources\Episodes\EpisodeResource;
use App\Models\Episode;
use App\Models\Podcast;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $user = User::factory()->create(['is_admin' => true]);
    $this->actingAs($user);
});

it('renders the episode create page for an authorized user', function () {
    $this->get(EpisodeResource::getUrl('create'))
        ->assertOk();
});

it('renders the episode edit page for an authorized user', function () {
    $podcast = Podcast::query()->create([
        'name' => 'Episode page coverage',
        'slug' => 'episode-page-coverage',
        'description' => 'Podcast description',
    ]);
    $episode = Episode::query()->create([
        'podcast_id' => $podcast->id,
        'title' => 'Episode page coverage',
        'slug' => 'episode-page-coverage',
        'description' => 'Episode description',
    ]);

    $this->get(EpisodeResource::getUrl('edit', ['record' => $episode]))
        ->assertOk();
});
