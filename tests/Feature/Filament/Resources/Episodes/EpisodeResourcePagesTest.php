<?php

use App\Filament\Resources\Episodes\EpisodeResource;
use App\Models\Episode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    $user = User::factory()->create(['is_admin' => true]);
    actingAs($user);
});

it('renders the episode create page for an authorized user', function () {
    get(EpisodeResource::getUrl('create'))
        ->assertOk();
});

it('renders the episode edit page for an authorized user', function () {
    $episode = Episode::factory()->create();

    get(EpisodeResource::getUrl('edit', ['record' => $episode]))
        ->assertOk();
});
