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
        '/up',
        '/uses',
    ];

    foreach ($routes as $route) {
        $response = Http::timeout(15)->get($baseUrl.$route);

        $expectation = expect($response->successful());
        $expectation->toBeTrue($route);
    }
});

it('redirects the admin entry point to authentication', function (): void {
    $baseUrl = productionSmokeBaseUrl();
    assert($baseUrl !== null);
    $request = Http::withoutRedirecting();
    $request->timeout(15);
    $response = $request->get($baseUrl.'/admin');

    $expectation = expect($response->status());
    $expectation->toBe(302);
    $expectation = expect($response->header('Location'));
    $expectation->toMatch('#/admin/login$#');
});

it('returns the required security headers on public routes', function (): void {
    $baseUrl = productionSmokeBaseUrl();
    assert($baseUrl !== null);
    $routes = ['/', '/about', '/archive', '/blog', '/contact', '/projects'];

    foreach ($routes as $route) {
        $request = Http::timeout(15);
        $response = $request->get($baseUrl.$route);
        /** @var array<string, list<string>> $headers */
        $headers = $response->headers();
        $frameOptionHeader = $headers['x-frame-options'][0] ?? '';
        $frameOptions = array_map(trim(...), explode(',', $frameOptionHeader));
        $contentSecurityPolicy = $headers['content-security-policy'][0] ?? '';

        $frameExpectation = expect($frameOptions);
        $notExpectation = $frameExpectation->not;
        $notExpectation->toBe(['']);
        $frameExpectation = expect($frameOptions);
        $eachExpectation = $frameExpectation->each;
        $eachExpectation->toBeIn(['SAMEORIGIN', 'DENY']);
        expect($contentSecurityPolicy)->toMatch("/frame-ancestors ('self'|'none')/");
        expect($headers['strict-transport-security'][0] ?? '')->toContain('max-age=31536000');
        expect($headers['referrer-policy'][0] ?? '')->toBe('strict-origin-when-cross-origin');
    }
});
