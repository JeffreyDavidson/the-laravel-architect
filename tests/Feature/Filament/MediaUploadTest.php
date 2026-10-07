<?php

use App\Enums\PublishStatus;
use App\Filament\Resources\Projects\Pages\CreateProject;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Support\ImageFixtures;

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
        ->assertHasFormErrors(['featured_image_path']);

    expect(Storage::disk('public')->allFiles())
        ->toBeEmpty();
});

it('rejects an image above the pixel limit through the Filament project form', function () {
    livewire(CreateProject::class)
        ->fillForm([
            'title' => 'Project',
            'slug' => 'project',
            'description' => 'Description',
            'status' => PublishStatus::Draft,
            'featured_image_path' => UploadedFile::fake()->createWithContent('huge.png', ImageFixtures::blankPng(8000, 5001)),
        ])
        ->call('create')
        ->assertHasFormErrors(['featured_image_path' => 'This image is too large to process. Images can be at most 40 megapixels (8000 × 5000 px, for example). Resize it and upload it again.']);

    expect(Project::query()->count())
        ->toBe(0)
        ->and(Storage::disk('public')->allFiles())
        ->toBeEmpty();
});
