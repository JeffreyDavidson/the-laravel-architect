<?php

use App\Support\Content\BundledPostArtwork;

covers(BundledPostArtwork::class);

it('knows which post slugs ship with bundled artwork', function (string $slug, bool $bundled) {
    $exists = app(BundledPostArtwork::class)->exists($slug);

    expect($exists)
        ->toBe($bundled);
})->with([
    'hello world' => ['hello-world-why-im-starting-this-blog', true],
    'kansas to florida' => ['from-kansas-to-florida-a-developers-journey', true],
    'structure every project' => ['how-i-structure-every-laravel-project', true],
    'why laravel' => ['why-i-still-choose-laravel-in-2026', true],
    'fifteen years' => ['what-15-years-of-web-development-taught-me', true],
    'any other post' => ['a-brand-new-post', false],
]);

it('returns small, medium and large urls for a bundled post', function () {
    $this->withVite();

    $urls = app(BundledPostArtwork::class)->urls('hello-world-why-im-starting-this-blog');

    expect($urls)
        ->toHaveKeys(['small', 'medium', 'large'])
        ->and($urls['large'] ?? '')
        ->toContain('post-hello-world-1280');
});

it('has no urls for a post without bundled artwork', function () {
    $urls = app(BundledPostArtwork::class)->urls('a-brand-new-post');

    expect($urls)
        ->toBeNull();
});
