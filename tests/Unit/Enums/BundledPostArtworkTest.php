<?php

use App\Enums\BundledPostArtwork;

covers(BundledPostArtwork::class);

it('knows which post slugs ship with bundled artwork', function (string $slug, ?string $imageName) {
    $artwork = BundledPostArtwork::tryFrom($slug);

    expect($artwork?->imageName())
        ->toBe($imageName);
})->with([
    'hello world' => ['hello-world-why-im-starting-this-blog', 'post-hello-world'],
    'kansas to florida' => ['from-kansas-to-florida-a-developers-journey', 'post-kansas-florida'],
    'structure every project' => ['how-i-structure-every-laravel-project', 'home-writing-fallback'],
    'why laravel' => ['why-i-still-choose-laravel-in-2026', 'home-writing-review'],
    'fifteen years' => ['what-15-years-of-web-development-taught-me', 'home-writing-modules'],
    'any other post' => ['a-brand-new-post', null],
]);

it('lists the slugs of the posts with bundled artwork', function () {
    expect(BundledPostArtwork::slugs())->toBe([
        'hello-world-why-im-starting-this-blog',
        'from-kansas-to-florida-a-developers-journey',
        'how-i-structure-every-laravel-project',
        'why-i-still-choose-laravel-in-2026',
        'what-15-years-of-web-development-taught-me',
    ]);
});
