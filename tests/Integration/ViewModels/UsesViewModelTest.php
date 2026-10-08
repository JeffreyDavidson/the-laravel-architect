<?php

use App\ViewModels\UsesViewModel;
use Tests\Support\StructuredDataExpectations as Schema;

it('describes the uses page with breadcrumbs', function () {
    Schema::useFixedOrigin();

    $data = app(UsesViewModel::class)
        ->data();

    expect(Schema::graph($data['pageMeta']))->toBe([
        Schema::website(),
        Schema::page('WebPage', 'Uses', 'https://example.test/uses'),
        Schema::breadcrumbs([['Home', 'https://example.test'], ['Uses', 'https://example.test/uses']]),
    ]);
});

it('provides the existing page SEO metadata', function () {
    $data = app(UsesViewModel::class)
        ->data();

    expect($data['pageMeta']->seo->title)->toBe('Uses')
        ->and($data['pageMeta']->seo->description)
        ->toBe('The tools, hardware, and software Jeffrey Davidson uses for Laravel development, content creation, and everyday work.');
});

it('lists the uses sections in page order, each with items', function () {
    $sections = app(UsesViewModel::class)
        ->data()['sections'];

    expect(array_column($sections, 'id'))
        ->toBe(['hardware', 'development', 'content-creation', 'productivity', 'this-site'])
        ->and(array_filter($sections, fn (array $section): bool => $section['items'] === []))
        ->toBeEmpty();
});
