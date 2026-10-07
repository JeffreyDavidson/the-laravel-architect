<?php

use App\ViewModels\UsesViewModel;

it('provides the existing page SEO metadata', function () {
    $data = app(UsesViewModel::class)
        ->data();

    expect($data['seoSource']->title)->toBe('Uses')
        ->and($data['seoSource']->description)
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
