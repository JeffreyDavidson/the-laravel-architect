<?php

use App\Services\ProjectImageVariants;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
});

it('generates responsive webp variants without replacing the original image', function () {
    $image = UploadedFile::fake()->image('project.png', 1280, 72);

    Storage::disk('public')->put('projects/project.png', $image->getContent());

    $generated = app(ProjectImageVariants::class)->generate('projects/project.png');

    expect($generated)->toBeTrue();
    Storage::disk('public')->assertExists([
        'projects/responsive/project-640.webp',
        'projects/responsive/project-1280.webp',
        'projects/project.png',
    ]);

    $small = getimagesizefromstring(
        Storage::disk('public')->get('projects/responsive/project-640.webp'),
    );
    $large = getimagesizefromstring(
        Storage::disk('public')->get('projects/responsive/project-1280.webp'),
    );

    expect($small)->toMatchArray([640, 36])
        ->and($small['mime'])->toBe('image/webp')
        ->and($large)->toMatchArray([1280, 72])
        ->and($large['mime'])->toBe('image/webp');
});

it('returns a srcset only for generated variants that exist', function () {
    Storage::disk('public')->put('projects/responsive/project-640.webp', 'small');

    $srcset = app(ProjectImageVariants::class)->srcset('projects/project.png');

    expect($srcset)
        ->toContain('projects/responsive/project-640.webp')
        ->toContain(' 640w')
        ->not->toContain('1280w');
});

it('does not upscale images to create responsive variants', function () {
    $image = UploadedFile::fake()->image('small.png', 800, 45);
    Storage::disk('public')->put('projects/small.png', $image->getContent());

    app(ProjectImageVariants::class)->generate('projects/small.png');

    Storage::disk('public')->assertExists('projects/responsive/small-640.webp');
    Storage::disk('public')->assertMissing('projects/responsive/small-1280.webp');
});

it('deletes generated variants without deleting the original image', function () {
    Storage::disk('public')->put('projects/project.png', 'original');
    Storage::disk('public')->put('projects/responsive/project-640.webp', 'small');
    Storage::disk('public')->put('projects/responsive/project-1280.webp', 'large');

    app(ProjectImageVariants::class)->delete('projects/project.png');

    Storage::disk('public')->assertMissing([
        'projects/responsive/project-640.webp',
        'projects/responsive/project-1280.webp',
    ]);
    Storage::disk('public')->assertExists('projects/project.png');
});
