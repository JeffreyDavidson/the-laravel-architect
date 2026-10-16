<?php

use App\Actions\RepairImageVariants;
use App\Enums\MediaHealthType;
use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => Storage::fake('public'));

function repairableProject(?string $path): Project
{
    return Project::withoutEvents(fn (): Project => Project::factory()->create([
        'slug' => 'repairable-project',
        'featured_image_path' => $path,
    ]));
}

it('regenerates the responsive variants of a stored image', function () {
    Storage::disk('public')->put('projects/cover.webp', UploadedFile::fake()->image('cover.webp', 1600, 900)
        ->getContent());
    $project = repairableProject('projects/cover.webp');

    $repaired = app(RepairImageVariants::class)->handle(MediaHealthType::Project, (string) $project->id);

    expect($repaired)->toBeTrue();
    Storage::disk('public')->assertExists('projects/responsive/cover-640.webp');
});

it('reports failure when the record has no stored image to repair', function (?string $path) {
    // Arrange
    $project = repairableProject($path);

    // Act
    $repaired = app(RepairImageVariants::class)->handle(MediaHealthType::Project, (string) $project->id);

    // Assert
    expect($repaired)->toBeFalse();
})->with([
    'no image' => [null],
    'missing file' => ['projects/gone.webp'],
]);

it('reports failure for an unknown record', function () {
    // Arrange
    repairableProject(null);

    // Act
    $repaired = app(RepairImageVariants::class)->handle(MediaHealthType::Project, '999');

    // Assert
    expect($repaired)->toBeFalse();
});
