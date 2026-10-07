<?php

use App\Data\MediaHealthRecord;
use App\Enums\MediaHealthStatus;
use App\Enums\MediaHealthType;
use App\Models\Project;
use App\Queries\MediaHealthQuery;
use App\Services\ImageUploadOptimizer;
use App\Services\ResponsiveImageVariants;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

pest()->use(RefreshDatabase::class);

covers(MediaHealthQuery::class);

beforeEach(fn () => Storage::fake('public'));

function mediaHealthProject(string $slug, ?string $path): Project
{
    return Project::withoutEvents(fn (): Project => Project::factory()->create([
        'slug' => $slug,
        'featured_image_path' => $path,
    ]));
}

it('reports the source, variant and overall status for each image state', function () {
    $optimizer = app(ImageUploadOptimizer::class);
    $healthyPath = $optimizer->store(UploadedFile::fake()->image('healthy.jpg', 1600, 900), 'projects', 'public');
    $repairPath = $optimizer->store(UploadedFile::fake()->image('repair.jpg', 1600, 900), 'projects', 'public');
    $largeImage = UploadedFile::fake()->image('large.png', 3000, 1000);
    Storage::disk('public')->put('projects/large.png', $largeImage->getContent());
    $variants = app(ResponsiveImageVariants::class);
    $variants->generate((string) $healthyPath);

    $healthy = mediaHealthProject('healthy', $healthyPath);
    $repair = mediaHealthProject('repair', $repairPath);
    $large = mediaHealthProject('large', 'projects/large.png');
    $missingFile = mediaHealthProject('missing-file', 'projects/gone.webp');
    $noImage = mediaHealthProject('no-image', null);

    $records = app(MediaHealthQuery::class)->get(MediaHealthType::Project);

    $summary = collect($records)
        ->mapWithKeys(fn (MediaHealthRecord $record): array => [$record->key() => [
            $record->sourceStatus->value,
            $record->variantStatus->value,
            $record->status->value,
            $record->status->getColor(),
            $record->repairable,
        ]])
        ->all();

    expect($summary)
        ->toBe([
            "project:{$healthy->id}" => ['optimized', 'ready', 'healthy', 'success', false],
            "project:{$repair->id}" => ['optimized', 'missing', 'needs_repair', 'warning', true],
            "project:{$large->id}" => ['needs_optimization', 'missing', 'reupload_required', 'danger', true],
            "project:{$missingFile->id}" => ['missing', 'unavailable', 'reupload_required', 'danger', false],
            "project:{$noImage->id}" => ['missing', 'unavailable', 'reupload_required', 'danger', false],
        ]);
});

it('returns only records with the requested health status', function () {
    $repairPath = app(ImageUploadOptimizer::class)->store(UploadedFile::fake()->image('repair.jpg', 1600, 900), 'projects', 'public');
    $repair = mediaHealthProject('repair', $repairPath);
    mediaHealthProject('no-image', null);

    $records = app(MediaHealthQuery::class)->get(status: MediaHealthStatus::NeedsRepair);

    expect(array_map(fn (MediaHealthRecord $record): string => $record->key(), $records))
        ->toBe(["project:{$repair->id}"]);
});

it('reports the dimensions and file size of a readable image and none for a missing one', function () {
    Storage::disk('public')->put('projects/large.png', UploadedFile::fake()->image('large.png', 3000, 1000)
        ->getContent());
    mediaHealthProject('large', 'projects/large.png');
    mediaHealthProject('missing-file', 'projects/gone.webp');

    [$large, $missing] = app(MediaHealthQuery::class)->get(MediaHealthType::Project);

    expect([$large->filename, $large->width, $large->height, $large->fileSize])
        ->toBe(['large.png', 3000, 1000, Storage::disk('public')->size('projects/large.png')])
        ->and([$missing->filename, $missing->width, $missing->height, $missing->fileSize])
        ->toBe(['gone.webp', null, null, null]);
});
