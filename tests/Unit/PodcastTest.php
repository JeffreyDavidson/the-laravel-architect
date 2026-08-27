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

it('provides responsive fallback artwork for known podcasts', function (string $slug, string $prefix) {
    $podcast = new Podcast(['slug' => $slug]);

    expect($podcast->fallback_cover_image_srcset)
        ->toContain(Vite::asset("resources/images/{$prefix}-128.webp").' 128w')
        ->toContain(Vite::asset("resources/images/{$prefix}-320.webp").' 320w')
        ->toContain(Vite::asset("resources/images/{$prefix}-512.webp").' 512w');
})->with([
    ['coffee-with-the-laravel-architect', 'podcast-coffee-logo'],
    ['embracing-cloudy-days', 'podcast-cloudy-logo'],
]);
