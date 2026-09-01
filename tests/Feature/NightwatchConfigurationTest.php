<?php

use Monolog\Handler\NullHandler;

it('uses privacy-conscious Nightwatch defaults', function () {
    expect(config('nightwatch.enabled'))->toBeFalse()
        ->and(config('nightwatch.token'))->toBeNull()
        ->and(config('nightwatch.capture_request_payload'))->toBeFalse()
        ->and(config('nightwatch.capture_exception_source_code'))->toBeFalse()
        ->and(config('nightwatch.filtering.ignore_mail'))->toBeTrue()
        ->and(config('logging.channels.nightwatch'))->toMatchArray([
            'driver' => 'monolog',
            'handler' => NullHandler::class,
        ])
        ->and(config('nightwatch.sampling.requests'))->toBe(0.1)
        ->and(config('nightwatch.redact_payload_fields'))->toContain(
            '_token',
            'password',
            'password_confirmation',
            'email',
            'name',
            'message',
        )
        ->and(config('nightwatch.redact_headers'))->toContain(
            'Authorization',
            'Cookie',
            'Proxy-Authorization',
            'X-XSRF-TOKEN',
            'X-Forwarded-For',
            'X-Real-IP',
            'CF-Connecting-IP',
            'True-Client-IP',
        );
});
