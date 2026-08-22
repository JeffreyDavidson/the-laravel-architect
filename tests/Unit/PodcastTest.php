<?php

use App\Models\Podcast;

it('uses optimized fallback artwork for known podcasts', function (string $slug, string $expected) {
    $podcast = new Podcast(['slug' => $slug]);

    expect($podcast->cover_image_url)->toBe($expected)
        ->and(public_path(ltrim($expected, '/')))->toBeFile();
})->with([
    ['coffee-with-the-laravel-architect', '/images/podcast-coffee-logo-512.webp'],
    ['embracing-cloudy-days', '/images/podcast-cloudy-logo-512.webp'],
]);
