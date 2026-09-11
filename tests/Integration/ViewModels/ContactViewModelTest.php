<?php

use App\ViewModels\ContactViewModel;

it('provides the existing page SEO metadata', function () {
    $data = app(ContactViewModel::class)
        ->data();

    expect($data['seoSource']->title)->toBe('Contact')
        ->and($data['seoSource']->description)->toBe('Get in touch with Jeffrey Davidson for freelance Laravel development, consulting, legacy modernization, or just to say hello.');
});
