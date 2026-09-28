<?php

use function Pest\Laravel\get;

it('allows crawlers in production and points them to the sitemap', function () {
    config()->set('app.deployment_environment', 'production');

    $response = get('/robots.txt');

    $response
        ->assertOk()
        ->assertHeader('Content-Type', 'text/plain; charset=UTF-8');
    expect($response->getContent())
        ->toBe(implode("\n", [
            'User-agent: *',
            'Allow: /',
            '',
            'Disallow: /admin',
            'Disallow: /admin/*',
            '',
            'Sitemap: '.route('sitemap'),
            '',
        ]));
});

it('blocks all crawlers outside production', function (string $environment) {
    config()->set('app.deployment_environment', $environment);

    $response = get(route('robots'));

    $response
        ->assertOk()
        ->assertHeader('Content-Type', 'text/plain; charset=UTF-8');
    expect($response->getContent())
        ->toBe("User-agent: *\nDisallow: /\n");
})->with([
    'staging',
    'local',
]);

it('is not shadowed by a static robots file', function () {
    expect(public_path('robots.txt'))
        ->not->toBeFile();
});
