<?php

use App\Filament\Resources\Podcasts\PodcastResource;
use App\Models\Podcast;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    $user = User::factory()->create(['is_admin' => true]);
    actingAs($user);
});

it('renders the podcast create page for an authorized user', function () {
    get(PodcastResource::getUrl('create'))
        ->assertOk();
});

it('renders the podcast edit page for an authorized user', function () {
    $podcast = Podcast::factory()->create();

    get(PodcastResource::getUrl('edit', ['record' => $podcast]))
        ->assertOk();
});
