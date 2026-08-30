<?php

use App\Enums\ProjectStatus;
use App\Filament\Resources\Projects\Pages\CreateProject;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

it('rejects project text inputs longer than their database columns', function (string $field) {
    $this->actingAs(User::factory()->create(['is_admin' => true]));

    livewire(CreateProject::class)
        ->fillForm([
            'title' => 'Project title',
            'slug' => 'project-title',
            'description' => 'Project description',
            'status' => ProjectStatus::Draft,
            $field => str_repeat('a', 256),
        ])
        ->call('create')
        ->assertHasFormErrors([$field => 'max']);

    expect(Project::query()->exists())->toBeFalse();
})->with([
    'title',
    'url',
    'github_url',
]);
