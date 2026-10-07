<?php

use App\Models\Project;
use App\Presenters\ProjectPresenter;
use App\Services\ResponsiveImageVariants;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

covers(ProjectPresenter::class);

it('links the uploaded featured image on the public disk', function (?string $path, ?string $expected) {
    Storage::fake('public');
    $project = new Project(['featured_image_path' => $path]);

    expect(ProjectPresenter::from($project)->featuredImageUrl())->toBe($expected === null ? null : Storage::disk('public')->url($expected));
})->with([
    'upload' => ['projects/image.png', 'projects/image.png'],
    'no upload' => [null, null],
    'blank path' => ['', null],
]);

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
