<?php

use App\Filament\Resources\Projects\ProjectResource;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $user = User::factory()->create(['is_admin' => true]);
    $this->actingAs($user);
});

it('renders the project create page for an authorized user', function () {
    $this->get(ProjectResource::getUrl('create'))
        ->assertOk();
});

it('renders the project edit page for an authorized user', function () {
    $project = Project::query()->create([
        'title' => 'Project page coverage',
        'slug' => 'project-page-coverage',
        'description' => 'Project description',
    ]);

    $this->get(ProjectResource::getUrl('edit', ['record' => $project]))
        ->assertOk();
});
