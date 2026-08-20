<?php

use App\Enums\ProjectStatus;
use App\Filament\Resources\Projects\Pages\CreateProject;
use App\Filament\Resources\Projects\Pages\EditProject;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $user = User::factory()->create(['is_admin' => true]);
    $this->actingAs($user);
});

it('creates a project through the resource form', function () {
    livewire(CreateProject::class)
        ->fillForm([
            'title' => 'New project',
            'slug' => 'new-project',
            'description' => 'A project description.',
            'content' => 'The full project write-up.',
            'status' => ProjectStatus::Draft,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Project::query()->sole()->only([
        'title',
        'slug',
        'description',
        'content',
        'status',
    ]))->toMatchArray([
        'title' => 'New project',
        'slug' => 'new-project',
        'description' => 'A project description.',
        'content' => 'The full project write-up.',
        'status' => ProjectStatus::Draft,
    ]);
});

it('updates a project through the resource form', function () {
    $project = Project::query()->create([
        'title' => 'Existing project',
        'slug' => 'existing-project',
        'description' => 'The original description.',
        'content' => 'The original write-up.',
        'status' => ProjectStatus::Draft,
    ]);

    livewire(EditProject::class, ['record' => $project->getRouteKey()])
        ->fillForm([
            'title' => 'Updated project',
            'slug' => 'updated-project',
            'description' => 'The updated description.',
            'content' => 'The updated write-up.',
            'status' => ProjectStatus::Published,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($project->refresh()->only([
        'title',
        'slug',
        'description',
        'content',
        'status',
    ]))->toMatchArray([
        'title' => 'Updated project',
        'slug' => 'updated-project',
        'description' => 'The updated description.',
        'content' => 'The updated write-up.',
        'status' => ProjectStatus::Published,
    ]);
});
