<?php

use App\Monitoring\RedactNightwatchException;
use Laravel\Nightwatch\Core;
use Laravel\Nightwatch\Records\Exception;

it('removes sensitive details from exception records and previews', function () {
    $nightwatch = app(Core::class);
    $nightwatch->executionState->exceptionPreview = 'Request failed for private@example.test?token=private-token';
    $exception = new Exception(
        class: RuntimeException::class,
        message: 'Request failed for private@example.test?token=private-token',
        code: 1,
        file: '/application/app/Services/YouTubeService.php',
        line: 25,
        handled: false,
    );

    $redacted = app(RedactNightwatchException::class)($exception);

    expect($redacted)->toBeTrue()
        ->and($exception->message)->toBe('Exception message redacted.')
        ->and($nightwatch->executionState->exceptionPreview)->toBe('Exception message redacted.')
        ->not->toContain('private@example.test', 'private-token');
});

it('does not replace an existing preview for a handled exception', function () {
    $nightwatch = app(Core::class);
    $nightwatch->executionState->exceptionPreview = 'Earlier unhandled exception.';
    $exception = new Exception(
        class: RuntimeException::class,
        message: 'Handled failure for private@example.test',
        code: 0,
        file: '/application/app/Services/RuntimeHealthMonitor.php',
        line: 36,
        handled: true,
    );

    app(RedactNightwatchException::class)($exception);

    expect($exception->message)->toBe('Exception message redacted.')
        ->and($nightwatch->executionState->exceptionPreview)->toBe('Earlier unhandled exception.');
});
