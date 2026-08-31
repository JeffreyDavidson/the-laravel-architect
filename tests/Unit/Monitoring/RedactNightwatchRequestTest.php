<?php

use App\Monitoring\RedactNightwatchRequest;
use Laravel\Nightwatch\Records\Request;
use Symfony\Component\HttpFoundation\FileBag;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\InputBag;

it('replaces sensitive request metadata with the route template', function () {
    $request = nightwatchRequest(
        url: 'https://thelaravelarchitect.com/newsletter/confirm/42/private-token?expires=123&signature=private-signature',
        routePath: '/newsletter/confirm/{subscriber}/{token}',
        ip: '203.0.113.10',
    );

    $redacted = app(RedactNightwatchRequest::class)($request);

    expect($redacted)->toBeTrue()
        ->and($request->url)->toBe('/newsletter/confirm/{subscriber}/{token}')
        ->and($request->ip)->toBe('');
});

it('does not retain arbitrary paths for unmatched routes', function () {
    $request = nightwatchRequest(
        url: 'https://thelaravelarchitect.com/private-value?token=secret',
        routePath: '',
        ip: '203.0.113.10',
    );

    app(RedactNightwatchRequest::class)($request);

    expect($request->url)->toBe('[unmatched route]')
        ->and($request->ip)->toBe('');
});

function nightwatchRequest(string $url, string $routePath, string $ip): Request
{
    return new Request(
        method: 'GET',
        url: $url,
        routeName: 'newsletter.confirm',
        routeMethods: ['GET', 'HEAD'],
        routeDomain: '',
        routePath: $routePath,
        routeAction: '',
        ip: $ip,
        duration: 1,
        statusCode: 200,
        requestSize: 0,
        responseSize: 0,
        headers: new HeaderBag,
        payload: new InputBag,
        files: new FileBag,
    );
}
