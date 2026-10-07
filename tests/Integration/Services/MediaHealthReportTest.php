<?php

use App\Models\Project;
use App\Services\ImageUploadOptimizer;
use App\Services\MediaHealthReport;
use App\Services\ResponsiveImageVariants;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

pest()->use(RefreshDatabase::class);

covers(MediaHealthReport::class);

beforeEach(fn () => Storage::fake('public'));

function mediaHealthProject(string $slug, ?string $path): Project
{
    return Project::withoutEvents(fn (): Project => Project::factory()->create([
        'slug' => $slug,
        'featured_image_path' => $path,
    ]));
}

it('reports the status keys and colors for each image state', function () {
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

    $records = app(MediaHealthReport::class)->records('project');

    $summary = array_map(
        fn (array $record): array => [
            $record['source_status'],
            $record['variants'],
            $record['status'],
            $record['status_color'],
            $record['repairable'],
        ],
        $records,
    );

    expect($summary)
        ->toBe([
            "project:{$healthy->id}" => ['optimized', 'ready', 'healthy', 'success', false],
            "project:{$repair->id}" => ['optimized', 'missing', 'needs_repair', 'warning', true],
            "project:{$large->id}" => ['needs_optimization', 'missing', 'reupload_required', 'danger', true],
            "project:{$missingFile->id}" => ['missing', 'unavailable', 'reupload_required', 'danger', false],
            "project:{$noImage->id}" => ['missing', 'unavailable', 'reupload_required', 'danger', false],
        ]);
});
