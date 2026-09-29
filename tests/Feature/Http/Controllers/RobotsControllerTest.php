<?php

use function Pest\Laravel\get;

it('allows crawlers in production, keeps them out of the admin and signed previews, and points them to the sitemap', function () {
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
            'Disallow: /preview/',
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

it('serves robots.txt like a static file without cookies and publicly cacheable', function (string $environment) {
    config()->set('app.deployment_environment', $environment);

    $response = get(route('robots'));

    $response
        ->assertOk()
        ->assertHeaderMissing('Set-Cookie')
        ->assertHeader('Cache-Control', 'max-age=3600, public')
        ->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeader('Content-Security-Policy');
    expect($response->headers->getCookies())
        ->toBeEmpty();
})->with([
    'production',
    'staging',
]);

it('keeps search results crawlable so their noindex tag can be read', function () {
    config()->set('app.deployment_environment', 'production');

    $content = get('/robots.txt')->getContent();

    expect($content)
        ->not->toContain('Disallow: /search');
});
