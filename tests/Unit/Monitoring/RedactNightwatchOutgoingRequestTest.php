<?php

use App\Monitoring\RedactNightwatchOutgoingRequest;
use Laravel\Nightwatch\Records\OutgoingRequest;

it('retains only the scheme and host in outgoing request telemetry', function () {
    $request = new OutgoingRequest(
        method: 'GET',
        url: 'https://private-user:private-password@api.example.test:8443/accounts/private-account/webhooks/private-token?key=private-api-key#private-fragment',
        duration: 1,
        requestSize: 0,
        responseSize: 0,
        statusCode: 200,
    );

    $redacted = app(RedactNightwatchOutgoingRequest::class)($request);

    expect($redacted)->toBeTrue()
        ->and($request->url)->toBe('https://api.example.test')
        ->not->toContain(
            'private-user',
            'private-password',
            '8443',
            'private-account',
            'private-token',
            'private-api-key',
            'private-fragment',
        );
});

it('fully redacts malformed outgoing request URLs', function () {
    $request = new OutgoingRequest(
        method: 'POST',
        url: 'private-token',
        duration: 1,
        requestSize: 0,
        responseSize: 0,
        statusCode: 500,
    );

    app(RedactNightwatchOutgoingRequest::class)($request);

    expect($request->url)->toBe('[redacted]');
});
