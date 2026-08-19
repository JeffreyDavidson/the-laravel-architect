<?php

use AppFilament\Resources\Projects\Pages\EditProject;
use AppModels\Project;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('public');

    $user = User::factory()->create(['is_admin' => true]);
    $this->actingAs($user);
});

it('deletes a project through the resource action and removes its featured image', function () {
    Storage::disk('public')->put('projects/project.png', 'image');

    $project = Project::query()->create([
        'title' => 'Project to delete',
        'slug' => 'project-to-delete',
        'description' => 'A project that should be deleted.',
        'featured_image_path' => 'projects/project.png',
    ]);

    livewire(EditProject::class, ['record' => $project->getRouteKey()])
        ->callAction(DeleteAction::class);

    expect(Project::query()->find($project->id))->toBeNull();
    Storage::disk('public')->assertMissing('projects/project.png');
});
