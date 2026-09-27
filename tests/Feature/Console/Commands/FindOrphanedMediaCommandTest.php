<?php

use App\Enums\PublishStatus;
use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('public');
});

it('reports orphaned files while preserving referenced sources and variants', function () {
    $referencedPath = 'projects/project.webp';
    Storage::disk('public')->put($referencedPath, UploadedFile::fake()->image('project.webp', 800, 400)
        ->getContent());
    Storage::disk('public')->put('projects/responsive/project-640.webp', 'variant');
    Storage::disk('public')->put('orphan/unused.png', 'unused');

    Project::withoutEvents(fn () => Project::query()->create([
        'title' => 'Project',
        'slug' => 'project',
        'description' => 'Description',
        'status' => PublishStatus::Published,
        'featured_image_path' => $referencedPath,
    ]));

    $this->artisanCommand('media:find-orphans')
        ->expectsOutputToContain('Orphaned: orphan/unused.png')
        ->expectsOutputToContain('Found 1 orphaned files')
        ->expectsOutputToContain('No files were deleted')
        ->expectsOutputToContain('Media storage requires review.')
        ->assertFailed();

    Storage::disk('public')->assertExists([
        $referencedPath,
        'projects/responsive/project-640.webp',
        'orphan/unused.png',
    ]);
});

it('deletes only orphaned files when requested', function () {
    $referencedPath = 'projects/project.webp';
    Storage::disk('public')->put($referencedPath, 'referenced');
    Storage::disk('public')->put('projects/unused.png', 'unused');
    touch(Storage::disk('public')->path('projects/unused.png'), now()->subDays(2)
        ->getTimestamp());

    Project::withoutEvents(fn () => Project::query()->create([
        'title' => 'Project',
        'slug' => 'project',
        'description' => 'Description',
        'status' => PublishStatus::Published,
        'featured_image_path' => $referencedPath,
    ]));

    $this->artisanCommand('media:find-orphans', ['--delete' => true])
        ->expectsOutputToContain('Deleted: projects/unused.png')
        ->expectsOutputToContain('Deleted 1 orphaned files')
        ->assertSuccessful();

    Storage::disk('public')->assertExists($referencedPath);
    Storage::disk('public')->assertMissing('projects/unused.png');
});

it('preserves embedded attachments, unmanaged files, and recent uploads', function () {
    foreach (['projects/attachment.png', 'attachments/download.pdf', 'projects/recent.png'] as $path) {
        Storage::disk('public')->put($path, 'file');
    }
    touch(Storage::disk('public')->path('projects/attachment.png'), now()->subDays(2)
        ->getTimestamp());
    touch(Storage::disk('public')->path('attachments/download.pdf'), now()->subDays(2)
        ->getTimestamp());
    Project::withoutEvents(fn () => Project::query()->create([
        'title' => 'Project', 'slug' => 'project', 'description' => 'Description',
        'content' => '![Screenshot](/storage/projects/attachment.png)',
        'status' => PublishStatus::Draft,
    ]));

    $this->artisanCommand('media:find-orphans', ['--delete' => true])
        ->expectsOutputToContain('Deleted 0 orphaned files')
        ->assertFailed();

    Storage::disk('public')->assertExists(['projects/attachment.png', 'attachments/download.pdf', 'projects/recent.png']);
});

it('reports missing referenced files without treating them as orphans', function () {
    Project::withoutEvents(fn () => Project::query()->create([
        'title' => 'Project',
        'slug' => 'project',
        'description' => 'Description',
        'status' => PublishStatus::Published,
        'featured_image_path' => 'projects/missing.webp',
    ]));

    $this->artisanCommand('media:find-orphans')
        ->expectsOutputToContain('Found 0 orphaned files')
        ->expectsOutputToContain('Missing referenced files: 1.')
        ->expectsOutputToContain('Media storage requires review.')
        ->assertFailed();
});

it('succeeds when all stored files are referenced', function () {
    $path = 'projects/project.webp';
    Storage::disk('public')->put($path, 'referenced');

    Project::withoutEvents(fn () => Project::query()->create([
        'title' => 'Project',
        'slug' => 'project',
        'description' => 'Description',
        'status' => PublishStatus::Published,
        'featured_image_path' => $path,
    ]));

    $this->artisanCommand('media:find-orphans')
        ->expectsOutputToContain('Found 0 orphaned files')
        ->assertSuccessful();
});
