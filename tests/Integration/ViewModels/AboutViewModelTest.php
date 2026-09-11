<?php

use App\ViewModels\AboutViewModel;

it('provides the existing page SEO metadata', function () {
    $data = app(AboutViewModel::class)
        ->data();

    expect($data['seoSource']->title)->toBe('About')
        ->and($data['seoSource']->description)->toBe('Meet Jeffrey Davidson — 15+ years of PHP experience, Laravel architect, podcaster, and dad. Building clean, maintainable applications and sharing the journey.');
});
