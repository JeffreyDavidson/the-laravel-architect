<?php

use App\Models\Category;
use App\Models\Post;
use App\Services\FeaturedImageGenerator;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
});

it('generates default and category-specific featured images on the public disk', function () {
    $defaultPost = new Post(['slug' => 'default-featured-image']);
    $laravelPost = new Post(['slug' => 'laravel-featured-image']);
    $laravelPost->setRelation('category', new Category([
        'name' => 'Laravel',
        'slug' => 'laravel',
    ]));

    $generator = app(FeaturedImageGenerator::class);

    $defaultPath = $generator->generate($defaultPost);
    $laravelPath = $generator->generate($laravelPost);

    expect($defaultPath)->toBe('featured-images/default-featured-image.png')
        ->and($laravelPath)->toBe('featured-images/laravel-featured-image.png');

    foreach ([$defaultPath, $laravelPath] as $path) {
        Storage::disk('public')->assertExists($path);

        $absolutePath = Storage::disk('public')->path($path);
        $dimensions = getimagesize($absolutePath);

        expect(Storage::disk('public')->size($path))->toBeGreaterThan(0)
            ->and($dimensions)->not->toBeFalse()
            ->and($dimensions[0])->toBe(1200)
            ->and($dimensions[1])->toBe(630)
            ->and($dimensions['mime'])->toBe('image/png');
    }

    expect(hash_file('sha256', Storage::disk('public')->path($defaultPath)))
        ->not->toBe(hash_file('sha256', Storage::disk('public')->path($laravelPath)));
});

it('rejects a post slug that could escape the featured image directory', function () {
    $post = new Post(['slug' => '../../outside']);

    expect(fn () => app(FeaturedImageGenerator::class)->generate($post))
        ->toThrow(UnexpectedValueException::class, 'Post slug must be a non-empty, normalized slug.');

    Storage::disk('public')->assertMissing('featured-images');
});
