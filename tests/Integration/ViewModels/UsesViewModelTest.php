<?php

use App\ViewModels\UsesViewModel;

it('provides the existing page SEO metadata', function () {
    $data = app(UsesViewModel::class)
        ->data();

    expect($data['seoSource']->title)->toBe('Uses')
        ->and($data['seoSource']->description)->toBe('The tools, hardware, and software Jeffrey Davidson uses for Laravel development, content creation, and everyday work.');
});
