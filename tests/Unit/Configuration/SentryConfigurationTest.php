<?php

it('uses privacy-conscious Sentry defaults', function () {
    expect(config('sentry.dsn'))->toBeEmpty()
        ->and(config('sentry.send_default_pii'))->toBeFalse()
        ->and(config('sentry.traces_sample_rate'))->toBe(0.0)
        ->and(config('sentry.profiles_sample_rate'))->toBe(0.0)
        ->and(config('sentry.tracing.sql_bindings'))->toBeFalse();
});
