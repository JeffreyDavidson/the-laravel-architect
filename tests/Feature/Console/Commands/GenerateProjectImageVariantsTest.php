<?php

use App\Enums\ProjectStatus;
use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
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
});
