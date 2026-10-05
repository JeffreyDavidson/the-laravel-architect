<?php

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Sleep;
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
        ->and($response->status())
        ->toBe(302);
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

it('retries a request once after a connection failure', function (): void {
    Sleep::fake();
    Http::fake(['https://thelaravelarchitect.com/up' => Http::sequence()
        ->pushFailedConnection()
        ->push('healthy')]);

    $response = DeploymentSmokeClient::request('https://thelaravelarchitect.com')
        ->get('/up');

    expect($response->body())->toBe('healthy');
    Http::assertSentCount(2);
    Sleep::assertSleptTimes(1);
});

it('fails when the connection fails again on retry', function (): void {
    Sleep::fake();
    Http::fake(['https://thelaravelarchitect.com/up' => Http::sequence()
        ->pushFailedConnection()
        ->pushFailedConnection()
        ->push('healthy')]);

    expect(fn () => DeploymentSmokeClient::request('https://thelaravelarchitect.com')
        ->get('/up'))
        ->toThrow(ConnectionException::class);
});

it('returns an error status without retrying it', function (int $status): void {
    Sleep::fake();
    Http::fake(['https://thelaravelarchitect.com/up' => Http::sequence()
        ->push('unavailable', $status)
        ->push('healthy')]);

    $response = DeploymentSmokeClient::request('https://thelaravelarchitect.com')
        ->get('/up');

    expect($response->status())->toBe($status);
    Http::assertSentCount(1);
    Sleep::assertNeverSlept();
})->with([404, 500, 503]);
