<?php

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\Support\DeploymentSmokeClient;

it('rejects unapproved origins before sending access credentials', function (string $origin): void {
    Http::preventStrayRequests();

    expect(fn () => DeploymentSmokeClient::request($origin, 'client', 'secret'))
        ->toThrow(InvalidArgumentException::class);
})->with([
    'http://staging.thelaravelarchitect.com',
    'https://staging.thelaravelarchitect.com.example.com',
    'https://staging.thelaravelarchitect.com@other.example',
    'https://staging.thelaravelarchitect.com:8443',
    'https://staging.thelaravelarchitect.com/path',
]);

it('requires both staging access credentials', function (string|false $clientId, string|false $clientSecret): void {
    expect(fn () => DeploymentSmokeClient::request('https://staging.thelaravelarchitect.com', $clientId, $clientSecret))
        ->toThrow(InvalidArgumentException::class);
})->with([[false, false], ['client', false], [false, 'secret'], ['', 'secret']]);

it('sends staging credentials without following redirects', function (): void {
    Http::preventStrayRequests();
    Http::fake(['https://staging.thelaravelarchitect.com/up' => Http::response('', 302, ['Location' => 'https://other.example'])]);

    $request = DeploymentSmokeClient::request('https://staging.thelaravelarchitect.com', 'client', 'secret');
    $response = $request->get('/up');

    expect($request->getOptions()['allow_redirects'])->toBeFalse()
        ->and($response->status())->toBe(302);
    Http::assertSent(fn (Request $sent): bool => $sent->hasHeader('CF-Access-Client-Id', 'client')
        && $sent->hasHeader('CF-Access-Client-Secret', 'secret'));
});

it('omits staging credentials from production requests', function (): void {
    Http::preventStrayRequests();
    Http::fake(['https://thelaravelarchitect.com/up' => Http::response('healthy')]);

    $request = DeploymentSmokeClient::request('https://thelaravelarchitect.com', 'client', 'secret');
    $request->get('/up');

    Http::assertSent(fn (Request $sent): bool => ! $sent->hasHeader('CF-Access-Client-Id')
        && ! $sent->hasHeader('CF-Access-Client-Secret'));
});
