<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use JeffreyDavidson\CreatorKit\Filament\Resources\Episodes\Pages\EditEpisode;
use Tests\Support\PublishableFixtures;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => actingAs(User::factory()->create(['is_admin' => true])));

it('offers only the Transistor and YouTube media fields on an episode', function () {
    $episode = PublishableFixtures::ready('episode');

    livewire(EditEpisode::class, ['record' => $episode->getRouteKey()])
        ->assertFormFieldExists('transistor_url')
        ->assertFormFieldExists('youtube_url')
        ->assertFormFieldDoesNotExist('audio_url')
        ->assertFormFieldDoesNotExist('audio_path')
        ->assertFormFieldDoesNotExist('embed_url');
});
