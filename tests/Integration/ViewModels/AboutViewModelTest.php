<?php

use App\ViewModels\AboutViewModel;
use Tests\Support\StructuredDataExpectations as Schema;

it('provides the existing page SEO metadata', function () {
    $data = app(AboutViewModel::class)
        ->data();

    expect($data['pageMeta']->seo->title)->toBe('About')
        ->and($data['pageMeta']->seo->description)
        ->toBe('Meet Jeffrey Davidson — 15+ years of PHP experience, Laravel architect, podcaster, and dad. Building clean, maintainable applications and sharing the journey.');
});

it('describes the about page as the author profile with breadcrumbs', function () {
    Schema::useFixedOrigin();

    $data = app(AboutViewModel::class)
        ->data();

    expect(Schema::graph($data['pageMeta']))->toBe([
        Schema::website(),
        Schema::page('ProfilePage', 'About', 'https://example.test/about', isProfile: true),
        Schema::breadcrumbs([['Home', 'https://example.test'], ['About', 'https://example.test/about']]),
    ]);
});
