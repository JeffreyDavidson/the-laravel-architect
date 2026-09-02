<?php

use App\Enums\ProjectStatus;
use App\Models\Podcast;
use App\Models\Project;
use App\Services\ResponsiveImageVariants;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('public');
});

it('verifies responsive media using aggregate results', function () {
    $projectImage = UploadedFile::fake()->image('project.png', 1280, 72);
    $podcastImage = UploadedFile::fake()->image('podcast.png', 800, 45);
    Storage::disk('public')->put('projects/project.png', $projectImage->getContent());
    Storage::disk('public')->put('podcasts/podcast.png', $podcastImage->getContent());

    Project::withoutEvents(fn () => Project::query()->create([
        'title' => 'Project',
        'slug' => 'project',
        'description' => 'Description',
        'status' => ProjectStatus::Published,
        'featured_image_path' => 'projects/project.png',
    ]));
    Podcast::withoutEvents(fn () => Podcast::query()->create([
        'name' => 'Podcast',
        'slug' => 'podcast',
        'description' => 'Description',
        'cover_image_path' => 'podcasts/podcast.png',
    ]));

    $images = app(ResponsiveImageVariants::class);
    $images->generate('projects/project.png');
    $images->generate('podcasts/podcast.png');

    $this->artisan('media:verify-responsive-images')
        ->expectsOutputToContain('Projects: 1 checked, 1 verified, 0 failed.')
        ->expectsOutputToContain('Posts: 0 checked, 0 verified, 0 failed.')
        ->expectsOutputToContain('Podcasts: 1 checked, 1 verified, 0 failed.')
        ->expectsOutputToContain('Responsive image verification passed.')
        ->assertSuccessful();
});

it('fails without exposing media paths when required variants are missing', function () {
    $image = UploadedFile::fake()->image('private-project-name.png', 1280, 72);
    Storage::disk('public')->put('projects/private-project-name.png', $image->getContent());

    Project::withoutEvents(fn () => Project::query()->create([
        'title' => 'Project',
        'slug' => 'project',
        'description' => 'Description',
        'status' => ProjectStatus::Published,
        'featured_image_path' => 'projects/private-project-name.png',
    ]));

    $this->artisan('media:verify-responsive-images')
        ->expectsOutputToContain('Projects: 1 checked, 0 verified, 1 failed.')
        ->doesntExpectOutputToContain('private-project-name.png')
        ->expectsOutputToContain('Responsive image verification failed.')
        ->assertFailed();
});
