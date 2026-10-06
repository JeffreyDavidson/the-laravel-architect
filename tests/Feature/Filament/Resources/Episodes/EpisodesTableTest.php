<?php

use App\Filament\Resources\Episodes\Pages\ListEpisodes;
use App\Models\User;
use App\Support\Content\PreviewUrlGenerator;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\PublishableFixtures;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\freezeSecond;
use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

it('links the preview action to a signed preview of the episode', function () {
    freezeSecond();
    actingAs(User::factory()->create(['is_admin' => true]));
    $episode = PublishableFixtures::ready('episode');

    livewire(ListEpisodes::class)
        ->assertActionHasUrl(TestAction::make('preview')->table($episode), app(PreviewUrlGenerator::class)->for($episode))
        ->assertActionShouldOpenUrlInNewTab(TestAction::make('preview')->table($episode));
});
