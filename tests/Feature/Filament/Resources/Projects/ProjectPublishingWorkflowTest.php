<?php

use App\Enums\PublishStatus;
use App\Filament\Resources\Projects\Pages\EditProject;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    $this->actingAs(User::factory()->create(['is_admin' => true]));
});

it('publishes a project through Filament and exposes it publicly', function () {
    $project = Project::query()->create([
        'title' => 'Publishing workflow project',
        'slug' => 'publishing-workflow-project',
        'description' => 'A project that is ready to publish.',
        'status' => PublishStatus::Draft,
    ]);

    livewire(EditProject::class, ['record' => $project->getRouteKey()])
        ->fillForm(['status' => PublishStatus::Published])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($project->refresh()->status)->toBe(PublishStatus::Published);

    $this->get(route('projects.show', $project))->assertOk();
    $this->get('/sitemap.xml')->assertSeeHtml(route('projects.show', $project));
});

it('hides a project again when Filament changes it back to draft', function () {
    $project = Project::query()->create([
        'title' => 'Draft workflow project',
        'slug' => 'draft-workflow-project',
        'description' => 'A project that is no longer public.',
        'status' => PublishStatus::Published,
    ]);

    livewire(EditProject::class, ['record' => $project->getRouteKey()])
        ->fillForm(['status' => PublishStatus::Draft])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($project->refresh()->status)->toBe(PublishStatus::Draft);

    $this->get(route('projects.show', $project))->assertNotFound();
    $this->get('/sitemap.xml')->assertDontSeeHtml(route('projects.show', $project));
});
