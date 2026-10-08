<?php

use App\ViewModels\PrivacyViewModel;
use Tests\Support\StructuredDataExpectations as Schema;

it('provides the existing page SEO metadata', function () {
    $data = app(PrivacyViewModel::class)
        ->data();

    expect($data['pageMeta']->seo->title)->toBe('Privacy')
        ->and($data['pageMeta']->seo->description)
        ->toBe('How The Laravel Architect handles contact messages, newsletter subscriptions, observability, and essential site data.');
});

it('describes the privacy page with breadcrumbs', function () {
    Schema::useFixedOrigin();

    $data = app(PrivacyViewModel::class)
        ->data();

    expect(Schema::graph($data['pageMeta']))->toBe([
        Schema::website(),
        Schema::page('WebPage', 'Privacy', 'https://example.test/privacy'),
        Schema::breadcrumbs([['Home', 'https://example.test'], ['Privacy', 'https://example.test/privacy']]),
    ]);
});
