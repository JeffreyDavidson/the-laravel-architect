<?php

use App\ViewModels\ServiceViewModel;

it('provides the existing page SEO metadata', function () {
    $data = app(ServiceViewModel::class)
        ->data();

    expect($data['pageMeta']->seo->title)->toBe('Services')
        ->and($data['pageMeta']->seo->description)
        ->toBe('Laravel development, codebase modernization, and testing with Jeffrey Davidson. Build useful applications and make your next release easier.');
});
