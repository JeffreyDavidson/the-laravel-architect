<?php

use App\Monitoring\RedactNightwatchException;
use Illuminate\Database\QueryException;
use Laravel\Nightwatch\Core;
use Laravel\Nightwatch\Records\Exception;

it('removes database bindings from exception records and previews', function () {
    $nightwatch = app(Core::class);
    $nightwatch->executionState->exceptionPreview = 'SQL insert failed for private@example.test';
    $exception = new Exception(
        class: QueryException::class,
        message: 'SQL insert failed for private@example.test',
        code: 1,
        file: '/application/app/Actions/RequestNewsletterSubscription.php',
        line: 25,
        handled: false,
    );

    $redacted = app(RedactNightwatchException::class)($exception);

    expect($redacted)->toBeTrue()
        ->and($exception->message)->toBe('Database query failed.')
        ->and($nightwatch->executionState->exceptionPreview)->toBe('Database query failed.')
        ->not->toContain('private@example.test');
});

it('preserves safe application exception messages', function () {
    $exception = new Exception(
        class: RuntimeException::class,
        message: 'The scheduler heartbeat is stale.',
        code: 0,
        file: '/application/app/Services/RuntimeHealthMonitor.php',
        line: 36,
        handled: false,
    );

    app(RedactNightwatchException::class)($exception);

    expect($exception->message)->toBe('The scheduler heartbeat is stale.');
});
