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

beforeEach(fn () => actingAs(User::factory()->create(['is_admin' => true])));

it('links the view on site action to the public project URL', function () {
    $project = PublishableFixtures::ready('project');
    $project->publish();

    livewire(ListProjects::class)
        ->assertActionHasUrl(TestAction::make('view_on_site')->table($project), route('projects.show', $project))
        ->assertActionShouldOpenUrlInNewTab(TestAction::make('view_on_site')->table($project));
});

it('links the view on site action to a signed preview for a draft project', function () {
    freezeSecond();
    $project = PublishableFixtures::ready('project');

    livewire(ListProjects::class)
        ->assertActionHasUrl(TestAction::make('view_on_site')->table($project), app(PreviewUrlGenerator::class)->for($project))
        ->assertActionShouldOpenUrlInNewTab(TestAction::make('view_on_site')->table($project));
});
