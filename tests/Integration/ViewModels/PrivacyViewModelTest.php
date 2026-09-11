<?php

use App\ViewModels\PrivacyViewModel;

it('provides the existing page SEO metadata', function () {
    $data = app(PrivacyViewModel::class)
        ->data();

    expect($data['seoSource']->title)->toBe('Privacy')
        ->and($data['seoSource']->description)->toBe('How The Laravel Architect handles contact messages, newsletter subscriptions, analytics, and essential site data.');
});
