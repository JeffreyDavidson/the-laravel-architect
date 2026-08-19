<?php

use App\Filament\Resources\Podcasts\PodcastResource;
use App\Models\Podcast;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $user = User::factory()->create(['is_admin' => true]);
    $this->actingAs($user);
});

it('renders the podcast create page for an authorized user', function () {
    $this->get(PodcastResource::getUrl('create'))
        ->assertOk();
});

it('renders the podcast edit page for an authorized user', function () {
    $podcast = Podcast::query()->create([
        'name' => 'Podcast page coverage',
        'slug' => 'podcast-page-coverage',
        'description' => 'Podcast description',
    ]);

    $this->get(PodcastResource::getUrl('edit', ['record' => $podcast]))
        ->assertOk();
});
