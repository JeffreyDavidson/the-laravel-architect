<?php

use App\Models\Category;
use App\Models\Post;
use App\Services\OgImageGenerator;

it('generates a PNG image with the expected social dimensions', function () {
    $post = new Post(['title' => 'A Laravel architecture article']);
    $post->setRelation('category', new Category(['name' => 'Architecture']));

    $contents = app(OgImageGenerator::class)
        ->generate($post);
    $dimensions = getimagesizefromstring($contents);

    expect($contents)->toStartWith("\x89PNG\r\n\x1a\n")
        ->and($dimensions)->toMatchArray([0 => 1200, 1 => 630, 'mime' => 'image/png']);
});
