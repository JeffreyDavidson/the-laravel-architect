<?php

use App\Enums\PublishStatus;
use App\Filament\Resources\Projects\Pages\EditProject;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    actingAs(User::factory()->create(['is_admin' => true]));
});

it('publishes a project through Filament and exposes it publicly', function () {
    $project = Project::factory()->create();

    livewire(EditProject::class, ['record' => $project->getRouteKey()])
        ->callAction('publish')
        ->assertNotified('Project published');

    expect($project->refresh()
        ->status)->toBe(PublishStatus::Published);

    get(route('projects.show', $project))
        ->assertOk();
    get('/sitemap.xml')
        ->assertSeeHtml(route('projects.show', $project));
});

it('hides a project again when Filament unpublishes it', function () {
    $project = Project::factory()
        ->published()
        ->create();

    livewire(EditProject::class, ['record' => $project->getRouteKey()])
        ->callAction('unpublish')
        ->assertNotified('Project unpublished');

    expect($project->refresh()
        ->status)->toBe(PublishStatus::Draft);

    get(route('projects.show', $project))
        ->assertNotFound();
    get('/sitemap.xml')
        ->assertDontSeeHtml(route('projects.show', $project));
});
