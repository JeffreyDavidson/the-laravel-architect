<?php

use App\Monitoring\RedactNightwatchOutgoingRequest;
use Laravel\Nightwatch\Records\OutgoingRequest;

it('removes query parameters and fragments from outgoing request telemetry', function () {
    $request = new OutgoingRequest(
        method: 'GET',
        url: 'https://www.googleapis.com/youtube/v3/videos?key=private-api-key&id=private-video-id#private-fragment',
        duration: 1,
        requestSize: 0,
        responseSize: 0,
        statusCode: 200,
    );

    $redacted = app(RedactNightwatchOutgoingRequest::class)($request);

    expect($redacted)->toBeTrue()
        ->and($request->url)->toBe('https://www.googleapis.com/youtube/v3/videos')
        ->not->toContain('private-api-key', 'private-video-id', 'private-fragment');
});
