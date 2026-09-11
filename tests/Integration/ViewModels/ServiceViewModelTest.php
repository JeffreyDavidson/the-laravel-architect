<?php

use App\ViewModels\ServiceViewModel;

it('provides the existing page SEO metadata', function () {
    $data = app(ServiceViewModel::class)
        ->data();

    expect($data['seoSource']->title)->toBe('Services')
        ->and($data['seoSource']->description)->toBe('Laravel development, codebase modernization, and testing with Jeffrey Davidson. Build useful applications and make your next release easier.');
});
