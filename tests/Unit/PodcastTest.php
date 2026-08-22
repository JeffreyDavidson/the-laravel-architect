<?php

use App\Models\Podcast;
use Illuminate\Support\Facades\Vite;

it('uses optimized fallback artwork for known podcasts', function (string $slug, string $expected) {
    $podcast = new Podcast(['slug' => $slug]);

    expect($podcast->cover_image_url)->toBe(Vite::asset($expected))
        ->and(base_path($expected))->toBeFile();
})->with([
    ['coffee-with-the-laravel-architect', 'resources/images/podcast-coffee-logo-512.webp'],
    ['embracing-cloudy-days', 'resources/images/podcast-cloudy-logo-512.webp'],
]);
