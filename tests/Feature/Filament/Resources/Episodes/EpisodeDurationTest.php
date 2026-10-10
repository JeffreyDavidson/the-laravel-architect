<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use JeffreyDavidson\CreatorKit\Filament\Resources\Episodes\Pages\EditEpisode;
use Tests\Support\PublishableFixtures;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => actingAs(User::factory()->create(['is_admin' => true])));

it('saves an episode duration in seconds', function () {
    $episode = PublishableFixtures::ready('episode');

    livewire(EditEpisode::class, ['record' => $episode->getRouteKey()])
        ->fillForm(['duration_seconds' => 1534])
        ->call('save')
        ->assertHasNoFormErrors();

    $episode->refresh();

    expect($episode->getAttribute('duration_seconds'))
        ->toBe(1534);
});

it('rejects a duration that is not a whole, non-negative number of seconds', function (mixed $value) {
    $episode = PublishableFixtures::ready('episode');

    livewire(EditEpisode::class, ['record' => $episode->getRouteKey()])
        ->fillForm(['duration_seconds' => $value])
        ->call('save')
        ->assertHasFormErrors(['duration_seconds']);
})->with([
    'negative' => -1,
    'decimal' => 12.5,
    'text' => 'twenty',
    'too large' => 2147483648,
]);
