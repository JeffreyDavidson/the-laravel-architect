<?php

use App\Filament\Resources\Episodes\Pages\EditEpisode;
use App\Filament\Resources\Episodes\Pages\ListEpisodes;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\PublishableFixtures;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => actingAs(User::factory()->create(['is_admin' => true])));

it('saves a Transistor share URL for an episode', function () {
    $episode = PublishableFixtures::ready('episode');

    livewire(EditEpisode::class, ['record' => $episode->getRouteKey()])
        ->fillForm(['transistor_url' => 'https://share.transistor.fm/s/428dcd6b'])
        ->call('save')
        ->assertHasNoFormErrors();

    $episode->refresh();

    expect($episode->getAttribute('transistor_url'))
        ->toBe('https://share.transistor.fm/s/428dcd6b');
});

it('rejects a URL that is not a Transistor share URL', function (string $url) {
    $episode = PublishableFixtures::ready('episode');

    livewire(EditEpisode::class, ['record' => $episode->getRouteKey()])
        ->fillForm(['transistor_url' => $url])
        ->call('save')
        ->assertHasFormErrors(['transistor_url']);
})->with([
    'embed URL' => 'https://share.transistor.fm/e/428dcd6b',
    'other host' => 'https://example.com/s/428dcd6b',
]);

it('lists published episodes still missing a Transistor URL', function () {
    $missing = PublishableFixtures::ready('episode', ['transistor_url' => null, 'youtube_url' => 'https://www.youtube.com/watch?v=abcdefghijk']);
    $missing->publish();
    $added = PublishableFixtures::ready('episode', [
        'title' => 'Added episode',
        'slug' => 'added-episode',
        'transistor_url' => 'https://share.transistor.fm/s/428dcd6b',
    ]);
    $added->publish();
    $draft = PublishableFixtures::ready('episode', ['title' => 'Draft episode', 'slug' => 'draft-episode']);

    livewire(ListEpisodes::class)
        ->filterTable('missing_transistor_url')
        ->assertCanSeeTableRecords([$missing])
        ->assertCanNotSeeTableRecords([$added, $draft])
        ->assertTableColumnExists('transistor');
});

it('shows whether each episode has a valid Transistor URL', function (?string $transistorUrl, bool $available) {
    $episode = PublishableFixtures::ready('episode', ['transistor_url' => $transistorUrl]);

    livewire(ListEpisodes::class)
        ->assertTableColumnStateSet('transistor', $available, $episode);
})->with([
    'valid share URL' => ['https://share.transistor.fm/s/428dcd6b', true],
    'no URL' => [null, false],
    'not a share URL' => ['https://example.com/episode', false],
]);
