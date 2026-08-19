<?php

use App\Enums\ProjectStatus;
use App\Filament\Resources\Projects\Pages\EditProject;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->actingAs(User::factory()->create(['is_admin' => true]));
});

it('publishes a project through Filament and exposes it publicly', function () {
    $project = Project::query()->create([
        'title' => 'Publishing workflow project',
        'slug' => 'publishing-workflow-project',
        'description' => 'A project that is ready to publish.',
        'status' => ProjectStatus::Draft,
    ]);

    livewire(EditProject::class, ['record' => $project->getRouteKey()])
        ->fillForm(['status' => ProjectStatus::Published])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($project->refresh()->status)->toBe(ProjectStatus::Published);

    $this->get(route('projects.show', $project))->assertOk();
    $this->get('/sitemap.xml')->assertSee(route('projects.show', $project), false);
});

it('hides a project again when Filament changes it back to draft', function () {
    $project = Project::query()->create([
        'title' => 'Draft workflow project',
        'slug' => 'draft-workflow-project',
        'description' => 'A project that is no longer public.',
        'status' => ProjectStatus::Published,
    ]);

    livewire(EditProject::class, ['record' => $project->getRouteKey()])
        ->fillForm(['status' => ProjectStatus::Draft])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($project->refresh()->status)->toBe(ProjectStatus::Draft);

    $this->get(route('projects.show', $project))->assertNotFound();
    $this->get('/sitemap.xml')->assertDontSee(route('projects.show', $project), false);
});
