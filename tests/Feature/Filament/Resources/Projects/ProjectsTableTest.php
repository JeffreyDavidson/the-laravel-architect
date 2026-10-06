<?php

use App\Filament\Resources\Projects\Pages\ListProjects;
use App\Models\User;
use App\Support\Content\PreviewUrlGenerator;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\PublishableFixtures;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\freezeSecond;
use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

it('links the preview action to a signed preview of the project', function () {
    freezeSecond();
    actingAs(User::factory()->create(['is_admin' => true]));
    $project = PublishableFixtures::ready('project');

    livewire(ListProjects::class)
        ->assertActionHasUrl(TestAction::make('preview')->table($project), app(PreviewUrlGenerator::class)->for($project))
        ->assertActionShouldOpenUrlInNewTab(TestAction::make('preview')->table($project));
});
