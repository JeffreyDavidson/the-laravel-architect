<?php

use Illuminate\Support\Facades\Http;

pest()->group('production');

function productionSmokeBaseUrl(): ?string
{
    $baseUrl = getenv('PRODUCTION_BASE_URL');

    return is_string($baseUrl) && $baseUrl !== '' ? rtrim($baseUrl, '/') : null;
}

beforeEach(function (): void {
    if (productionSmokeBaseUrl() === null) {
        $this->markTestSkipped('Set PRODUCTION_BASE_URL to run production smoke tests.');
    }
});

it('serves the critical public routes', function (): void {
    $baseUrl = productionSmokeBaseUrl();
    assert($baseUrl !== null);
    $routes = [
        '/',
        '/about',
        '/archive',
        '/blog',
        '/contact',
        '/newsletter',
        '/newsletter/rss',
        '/podcasts',
        '/privacy',
        '/projects',
        '/rss',
        '/search?q=accessibility',
        '/services',
        '/sitemap.xml',
        '/uses',
    ];

    foreach ($routes as $route) {
        $response = Http::timeout(15)->get($baseUrl.$route);

        expect($response->successful())->toBeTrue($route);
    }
});

it('redirects the admin entry point to authentication', function (): void {
    $baseUrl = productionSmokeBaseUrl();
    assert($baseUrl !== null);
    $response = Http::withoutRedirecting()->timeout(15)->get($baseUrl.'/admin');

    expect($response->status())->toBe(302)
        ->and($response->header('Location'))->toMatch('#/admin/login$#');
});

it('returns the required security headers on public routes', function (): void {
    $baseUrl = productionSmokeBaseUrl();
    assert($baseUrl !== null);
    $routes = ['/', '/about', '/archive', '/blog', '/contact', '/projects'];

    foreach ($routes as $route) {
        /** @var array<string, list<string>> $headers */
        $headers = Http::timeout(15)->get($baseUrl.$route)->headers();
        $frameOptions = array_map(trim(...), explode(',', $headers['x-frame-options'][0] ?? ''));
        $contentSecurityPolicy = $headers['content-security-policy'][0] ?? '';

        expect($frameOptions)->not->toBe([''])
            ->and($frameOptions)->each->toBeIn(['SAMEORIGIN', 'DENY'])
            ->and($contentSecurityPolicy)->toMatch("/frame-ancestors ('self'|'none')/")
            ->and($headers['strict-transport-security'][0] ?? '')->toContain('max-age=31536000')
            ->and($headers['referrer-policy'][0] ?? '')->toBe('strict-origin-when-cross-origin');
    }
});
