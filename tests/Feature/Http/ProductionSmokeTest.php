<?php

use Illuminate\Http\Client\PendingRequest;
use Tests\Support\DeploymentSmokeClient;

pest()->group('production');

function productionSmokeBaseUrl(): ?string
{
    $baseUrl = getenv('PRODUCTION_BASE_URL');

    return is_string($baseUrl) && $baseUrl !== '' ? rtrim($baseUrl, '/') : null;
}

function productionSmokeRequest(string $baseUrl): PendingRequest
{
    return DeploymentSmokeClient::request($baseUrl, getenv('CF_ACCESS_CLIENT_ID'), getenv('CF_ACCESS_CLIENT_SECRET'));
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
        $request = productionSmokeRequest($baseUrl);
        $response = $request->get($route);

        $expectation = expect($response->successful());
        $expectation->toBeTrue($route);
    }
});

it('redirects the admin entry point to authentication', function (): void {
    $baseUrl = productionSmokeBaseUrl();
    assert($baseUrl !== null);
    $request = productionSmokeRequest($baseUrl);
    $response = $request->get('/admin');

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
        $request = productionSmokeRequest($baseUrl);
        $response = $request->get($route);

        expect($response->successful())
            ->toBeTrue(
                "Expected {$route} to return a successful response; received HTTP {$response->status()}."
            );

        /** @var array<string, list<string>> $headers */
        $headers = $response->headers();
        $frameOptionHeader = $headers['x-frame-options'][0] ?? '';
        $frameOptions = array_map(trim(...), explode(',', $frameOptionHeader));
        $contentSecurityPolicy = $headers['content-security-policy'][0] ?? '';

        expect($frameOptions)
            ->not
            ->toBe(
                [''],
                "Expected {$route} to include a non-empty X-Frame-Options header; received HTTP {$response->status()}."
            );
        $frameExpectation = expect($frameOptions);
        $eachExpectation = $frameExpectation->each;
        $eachExpectation->toBeIn(['SAMEORIGIN', 'DENY']);
        expect($contentSecurityPolicy)
            ->toMatch("/frame-ancestors ('self'|'none')/")
            ->and($headers['strict-transport-security'][0] ?? '')
            ->toContain('max-age=31536000')
            ->and($headers['referrer-policy'][0] ?? '')
            ->toBe('strict-origin-when-cross-origin');
    }
});
