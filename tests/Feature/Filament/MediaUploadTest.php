<?php

use App\Enums\PublishStatus;
use App\Filament\Resources\Projects\Pages\CreateProject;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('public');

    $user = User::factory()->create(['is_admin' => true]);
    $this->actingAs($user);
});

it('stores a validated image through the Filament project form', function () {
    livewire(CreateProject::class)
        ->fillForm([
            'title' => 'Project',
            'slug' => 'project',
            'description' => 'Description',
            'status' => PublishStatus::Draft,
            'featured_image_path' => UploadedFile::fake()->image('project.jpg'),
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $path = Project::query()->sole()
        ->featured_image_path;

    if (! is_string($path)) {
        throw new RuntimeException('Expected a stored project image path.');
    }

    expect($path)
        ->toStartWith('projects/')
        ->toEndWith('.webp');
    Storage::disk('public')->assertExists($path);
});

it('rejects an oversized image through the Filament project form', function () {
    livewire(CreateProject::class)
        ->fillForm([
            'title' => 'Project',
            'slug' => 'project',
            'description' => 'Description',
            'status' => PublishStatus::Draft,
            'featured_image_path' => UploadedFile::fake()->image('project.jpg')
                ->size(10241),
        ])
        ->call('create')
        ->assertHasFormErrors(['featured_image_path']);

    expect(Project::query()->exists())->toBeFalse();
});
