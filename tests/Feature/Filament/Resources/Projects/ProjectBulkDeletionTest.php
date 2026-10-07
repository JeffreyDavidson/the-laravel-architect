<?php

use App\Filament\Resources\Projects\Pages\ListProjects;
use App\Models\Project;
use App\Models\User;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('public');
    actingAs(User::factory()->create(['is_admin' => true]));
});

it('permanently deletes trashed projects and their featured images through the table bulk action', function () {
    Storage::disk('public')->put('projects/first.png', 'image');
    Storage::disk('public')->put('projects/second.png', 'image');

    $projects = Project::factory()
        ->count(2)
        ->sequence(
            ['featured_image_path' => 'projects/first.png'],
            ['featured_image_path' => 'projects/second.png'],
        )
        ->create();

    $projects->each->delete();

    livewire(ListProjects::class)
        ->filterTable('trashed', false)
        ->selectTableRecords($projects)
        ->callAction(TestAction::make(ForceDeleteBulkAction::class)->table()
            ->bulk());

    expect(Project::withTrashed()->whereKey($projects->pluck('id'))
        ->count())->toBe(0);
    Storage::disk('public')->assertMissing('projects/first.png');
    Storage::disk('public')->assertMissing('projects/second.png');
});
