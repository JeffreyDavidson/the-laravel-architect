<?php

use App\Filament\Resources\Projects\Pages\EditProject;
use App\Models\Project;
use App\Models\User;
use Filament\Actions\ForceDeleteAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('public');

    $user = User::factory()->create(['is_admin' => true]);
    $this->actingAs($user);
});

it('permanently deletes a trashed project through the resource action and removes its featured image', function () {
    Storage::disk('public')->put('projects/project.png', 'image');

    $project = Project::query()->create([
        'title' => 'Project to delete',
        'slug' => 'project-to-delete',
        'description' => 'A project that should be deleted.',
        'featured_image_path' => 'projects/project.png',
    ]);

    $project->delete();

    livewire(EditProject::class, ['record' => $project->getRouteKey()])
        ->callAction(ForceDeleteAction::class);

    expect(Project::withTrashed()->find($project->id))->toBeNull();
    Storage::disk('public')->assertMissing('projects/project.png');
});
