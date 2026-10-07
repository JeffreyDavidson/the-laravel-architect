<?php

use App\Models\Category;
use App\Models\Post;
use App\Services\FeaturedImageGenerator;
use Illuminate\Support\Facades\Storage;

it('draws default and category-specific featured images as PNG bytes without writing files', function () {
    Storage::fake('public');
    $defaultPost = new Post(['slug' => 'default-featured-image']);
    $laravelPost = new Post(['slug' => 'laravel-featured-image']);
    $laravelPost->setRelation('category', new Category([
        'name' => 'Laravel',
        'slug' => 'laravel',
    ]));

    $generator = app(FeaturedImageGenerator::class);

    $defaultImage = $generator->generate($defaultPost);
    $laravelImage = $generator->generate($laravelPost);

    foreach ([$defaultImage, $laravelImage] as $image) {
        expect(getimagesizefromstring($image))
            ->toMatchArray([0 => 1200, 1 => 630, 'mime' => 'image/png']);
    }

    expect(hash('sha256', $defaultImage))
        ->not->toBe(hash('sha256', $laravelImage))
        ->and(Storage::disk('public')->allFiles())
        ->toBeEmpty();
});
