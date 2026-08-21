<?php

use App\Filament\Resources\Projects\Pages\ListProjects;
use App\Models\Project;
use App\Models\User;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('public');
    $this->actingAs(User::factory()->create(['is_admin' => true]));
});

it('deletes selected projects and their featured images through the table bulk action', function () {
    Storage::disk('public')->put('projects/first.png', 'image');
    Storage::disk('public')->put('projects/second.png', 'image');

    $projects = collect([
        Project::query()->create([
            'title' => 'First project',
            'slug' => 'first-project',
            'description' => 'The first project.',
            'featured_image_path' => 'projects/first.png',
        ]),
        Project::query()->create([
            'title' => 'Second project',
            'slug' => 'second-project',
            'description' => 'The second project.',
            'featured_image_path' => 'projects/second.png',
        ]),
    ]);

    livewire(ListProjects::class)
        ->selectTableRecords($projects)
        ->callAction(TestAction::make(DeleteBulkAction::class)->table()->bulk());

    expect(Project::query()->whereKey($projects->pluck('id'))->count())->toBe(0);
    Storage::disk('public')->assertMissing('projects/first.png');
    Storage::disk('public')->assertMissing('projects/second.png');
});
