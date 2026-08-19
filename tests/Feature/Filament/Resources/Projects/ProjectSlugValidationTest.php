<?php

use App\Enums\ProjectStatus;
use App\Filament\Resources\Projects\Pages\CreateProject;
use App\Filament\Resources\Projects\Pages\EditProject;
use App\Models\Project;
use App\Models\User;
use Database\Seeders\ShieldSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(ShieldSeeder::class);

    $user = User::factory()->create(['is_admin' => true]);
    $user->assignRole('super_admin');
    $this->actingAs($user);
});

it('rejects non-normalized project slugs when creating a project', function (string $slug) {
    livewire(CreateProject::class)
        ->fillForm([
            'title' => 'Project title',
            'slug' => $slug,
            'description' => 'Project description',
            'status' => ProjectStatus::Draft,
        ])
        ->call('create')
        ->assertHasFormErrors(['slug' => 'regex']);

    expect(Project::query()->exists())->toBeFalse();
})->with([
    'path traversal' => '../project-title',
    'spaces' => 'project title',
    'uppercase characters' => 'Project-Title',
    'leading hyphen' => '-project-title',
    'trailing hyphen' => 'project-title-',
    'repeated hyphens' => 'project--title',
]);

it('accepts a normalized project slug when creating a project', function () {
    livewire(CreateProject::class)
        ->fillForm([
            'title' => 'Project title',
            'slug' => 'project-title-2',
            'description' => 'Project description',
            'status' => ProjectStatus::Draft,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Project::query()->sole()->slug)->toBe('project-title-2');
});

it('rejects a non-normalized project slug when editing a project', function () {
    $project = Project::query()->create([
        'title' => 'Project title',
        'slug' => 'project-title',
        'description' => 'Project description',
        'status' => ProjectStatus::Draft,
    ]);

    livewire(EditProject::class, ['record' => $project->getRouteKey()])
        ->fillForm(['slug' => '../project-title'])
        ->call('save')
        ->assertHasFormErrors(['slug' => 'regex']);

    expect($project->refresh()->slug)->toBe('project-title');
});
