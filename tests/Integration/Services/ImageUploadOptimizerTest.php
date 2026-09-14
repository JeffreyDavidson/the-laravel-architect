<?php

use App\Services\ImageUploadOptimizer;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

beforeEach(function () {
    Storage::fake('public');
});

it('stores a scaled webp copy in the requested directory', function () {
    $file = UploadedFile::fake()->image('project.png', 3200, 1800);

    $path = app(ImageUploadOptimizer::class)->store($file, 'projects', 'public');

    if (! is_string($path)) {
        throw new RuntimeException('The optimized image was not stored.');
    }

    expect($path)
        ->toStartWith('projects/')
        ->toEndWith('.webp');

    Storage::disk('public')->assertExists($path);

    expect(getimagesize(Storage::disk('public')->path($path)))
        ->toMatchArray([0 => 1600, 1 => 900, 'mime' => 'image/webp']);
});

it('does not store unsupported upload contents', function () {
    $file = UploadedFile::fake()->create('not-an-image.txt', 1, 'text/plain');

    $path = app(ImageUploadOptimizer::class)->store($file, 'projects', 'public');

    expect($path)->toBeNull()
        ->and(Storage::disk('public')->allFiles())->toBe([]);
});
