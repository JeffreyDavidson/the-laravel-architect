<?php

use App\Filament\Resources\Episodes\Pages\ListEpisodes;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\PublishableFixtures;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => actingAs(User::factory()->create(['is_admin' => true])));

it('lists draft and published episodes that still carry legacy media', function () {
    $upload = PublishableFixtures::ready('episode', ['title' => 'Upload', 'slug' => 'upload', 'audio_path' => 'episodes/audio/a.mp3']);
    $hosted = PublishableFixtures::ready('episode', ['title' => 'Hosted', 'slug' => 'hosted', 'audio_url' => 'https://cdn.example.com/a.mp3']);
    $embed = PublishableFixtures::ready('episode', ['title' => 'Embed', 'slug' => 'embed', 'embed_url' => 'https://open.spotify.com/embed/episode/1']);
    $embed->publish();
    $clean = PublishableFixtures::ready('episode', [
        'title' => 'Clean',
        'slug' => 'clean',
        'audio_url' => null,
        'transistor_url' => 'https://share.transistor.fm/s/428dcd6b',
    ]);

    livewire(ListEpisodes::class)
        ->filterTable('legacy_media')
        ->assertCanSeeTableRecords([$upload, $hosted, $embed])
        ->assertCanNotSeeTableRecords([$clean])
        ->assertTableColumnExists('legacy_media');
});

it('ignores blank legacy media values', function () {
    $blank = PublishableFixtures::ready('episode', [
        'title' => 'Blank',
        'slug' => 'blank',
        'audio_path' => '',
        'audio_url' => '',
        'embed_url' => '',
        'transistor_url' => 'https://share.transistor.fm/s/428dcd6b',
    ]);

    livewire(ListEpisodes::class)
        ->filterTable('legacy_media')
        ->assertCanNotSeeTableRecords([$blank]);
});
