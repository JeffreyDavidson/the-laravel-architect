<?php

use App\Enums\ProjectStatus;
use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

it('backfills responsive variants for existing project images', function () {
    Storage::fake('public');
    $image = UploadedFile::fake()->image('project.png', 1280, 72);
    Storage::disk('public')->put('projects/project.png', $image->getContent());
    Project::withoutEvents(fn () => Project::query()->create([
        'title' => 'Project',
        'slug' => 'project',
        'description' => 'Description',
        'status' => ProjectStatus::Published,
        'featured_image_path' => 'projects/project.png',
    ]));

    $this->artisan('projects:generate-image-variants')
        ->expectsOutputToContain('Generated responsive images for 1 project.')
        ->assertSuccessful();

    Storage::disk('public')->assertExists([
        'projects/responsive/project-640.webp',
        'projects/responsive/project-1280.webp',
    ]);

    $this->artisan('projects:generate-image-variants')
        ->expectsOutputToContain('Generated responsive images for 0 projects.')
        ->expectsOutputToContain('Skipped 1 already verified project.')
        ->assertSuccessful();

    $this->artisan('projects:generate-image-variants', ['--force' => true])
        ->expectsOutputToContain('Generated responsive images for 1 project.')
        ->doesntExpectOutputToContain('Skipped')
        ->assertSuccessful();
});

it('refuses to overlap another project image generation run', function () {
    $lock = Cache::lock('framework/command-projects:generate-image-variants', 60);

    expect($lock->get())->toBeTrue();

    try {
        $this->artisan('projects:generate-image-variants')
            ->expectsOutputToContain('The [projects:generate-image-variants] command is already running.')
            ->assertFailed();
    } finally {
        $lock->release();
    }
});
