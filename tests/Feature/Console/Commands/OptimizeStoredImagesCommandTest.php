<?php

use App\Enums\PublishStatus;
use App\Models\Podcast;
use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('public');
});

it('replaces legacy project images with optimized webp files', function () {
    $originalPath = 'legacy/project.png';
    Storage::disk('public')->put(
        $originalPath,
        UploadedFile::fake()->image('project.png', 2000, 1000)->getContent(),
    );

    Project::withoutEvents(fn () => Project::query()->create([
        'title' => 'Project',
        'slug' => 'project',
        'description' => 'Description',
        'status' => PublishStatus::Published,
        'featured_image_path' => $originalPath,
    ]));

    $this->artisanCommand('media:optimize-images')
        ->expectsOutputToContain('Optimized 1 project.')
        ->expectsOutputToContain('Stored image optimization completed successfully.')
        ->assertSuccessful();

    $project = Project::query()->sole();
    $optimizedPath = $project->featured_image_path;
    if (! is_string($optimizedPath)) {
        throw new RuntimeException('The optimized project image path was not saved.');
    }

    $image = getimagesize(Storage::disk('public')->path($optimizedPath));
    if ($image === false) {
        throw new RuntimeException('The optimized project image could not be read.');
    }

    expect($optimizedPath)
        ->toStartWith('projects/')
        ->toEndWith('.webp')
        ->and($image)->toMatchArray(['mime' => 'image/webp'])
        ->and($image[0])->toBeLessThanOrEqual(1600)
        ->and($image[1])->toBeLessThanOrEqual(1600);

    Storage::disk('public')->assertMissing($originalPath);
    Storage::disk('public')->assertExists($optimizedPath);
});

it('skips existing webp files unless forced', function () {
    $path = 'projects/already-optimized.webp';
    Storage::disk('public')->put($path, UploadedFile::fake()->image('project.webp', 800, 400)->getContent());

    Project::withoutEvents(fn () => Project::query()->create([
        'title' => 'Project',
        'slug' => 'project',
        'description' => 'Description',
        'status' => PublishStatus::Published,
        'featured_image_path' => $path,
    ]));

    $this->artisanCommand('media:optimize-images')
        ->expectsOutputToContain('Skipped 1 already optimized project.')
        ->assertSuccessful();

    expect(Project::query()->sole()->featured_image_path)->toBe($path);

    $this->artisanCommand('media:optimize-images', ['--force' => true])
        ->expectsOutputToContain('Optimized 1 project.')
        ->doesntExpectOutputToContain('already optimized')
        ->assertSuccessful();

    expect(Project::query()->sole()->featured_image_path)->not->toBe($path);
});

it('reports unsupported sources while continuing other media types', function () {
    Storage::disk('public')->put('projects/invalid.png', 'not an image');
    Storage::disk('public')->put(
        'podcasts/podcast.png',
        UploadedFile::fake()->image('podcast.png', 1200, 1200)->getContent(),
    );

    Project::withoutEvents(fn () => Project::query()->create([
        'title' => 'Project',
        'slug' => 'project',
        'description' => 'Description',
        'status' => PublishStatus::Published,
        'featured_image_path' => 'projects/invalid.png',
    ]));
    Podcast::withoutEvents(fn () => Podcast::query()->create([
        'name' => 'Podcast',
        'slug' => 'podcast',
        'description' => 'Description',
        'cover_image_path' => 'podcasts/podcast.png',
    ]));

    $this->artisanCommand('media:optimize-images')
        ->expectsOutputToContain('Skipped project 1: its source image could not be optimized.')
        ->expectsOutputToContain('Optimized 1 podcast.')
        ->expectsOutputToContain('Stored image optimization completed with failures.')
        ->assertFailed();

    expect(Podcast::query()->sole()->cover_image_path)
        ->toStartWith('podcasts/')
        ->toEndWith('.webp');
    Storage::disk('public')->assertExists('projects/invalid.png');
});

it('reports planned changes without writing in dry-run mode', function () {
    $path = 'projects/project.png';
    Storage::disk('public')->put($path, UploadedFile::fake()->image('project.png', 1800, 900)->getContent());

    Project::withoutEvents(fn () => Project::query()->create([
        'title' => 'Project',
        'slug' => 'project',
        'description' => 'Description',
        'status' => PublishStatus::Published,
        'featured_image_path' => $path,
    ]));

    $this->artisanCommand('media:optimize-images', ['--dry-run' => true])
        ->expectsOutputToContain('Would optimize 1 project.')
        ->expectsOutputToContain('Stored image optimization dry run completed successfully.')
        ->assertSuccessful();

    expect(Project::query()->sole()->featured_image_path)->toBe($path);
    Storage::disk('public')->assertMissing('projects/responsive/project-640.webp');
    Storage::disk('public')->assertExists($path);
});
