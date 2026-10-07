<?php

use App\Models\Project;
use App\Presenters\ProjectPresenter;
use App\Services\ResponsiveImageVariants;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

covers(ProjectPresenter::class);

it('uses the uploaded featured image with its responsive variants', function () {
    Storage::fake('public');
    $image = UploadedFile::fake()->image('project.png', 1600, 900);
    Storage::disk('public')->put('projects/project.png', $image->getContent());
    app(ResponsiveImageVariants::class)->generate('projects/project.png');
    $project = new Project(['featured_image_path' => 'projects/project.png']);

    $featuredImage = ProjectPresenter::from($project)->featuredImage();

    expect($featuredImage?->src)->toBe(Storage::disk('public')->url('projects/project.png'))
        ->and($featuredImage?->srcset)
        ->toContain('project-640.webp 640w')
        ->toContain('project-1280.webp 1280w');
});

it('has no featured image for a project without an upload', function () {
    $featuredImage = ProjectPresenter::from(new Project)->featuredImage();

    expect($featuredImage)->toBeNull();
});
