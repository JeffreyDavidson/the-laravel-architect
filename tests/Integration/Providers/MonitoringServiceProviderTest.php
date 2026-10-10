<?php

use App\Models\User;
use App\Support\Monitoring\Sentry\RedactSentryBreadcrumb;
use App\Support\Monitoring\Sentry\RedactSentryEvent;
use Illuminate\Support\Facades\Config;
use Laravel\Nightwatch\Core;
use Sentry\ClientBuilder;

it('registers pseudonymous authenticated user details with Nightwatch', function () {
    Config::set('app.key', 'private-application-key');
    $resolver = app(Core::class)->userDetailsResolver;
    if ($resolver === null) {
        throw new RuntimeException('Nightwatch user details resolver was not registered.');
    }
    $user = new User;
    $user->forceFill([
        'id' => 42,
        'name' => 'Private Administrator',
        'email' => 'private@example.test',
    ]);

    expect($resolver($user))->toBe([
        'id' => hash_hmac('sha256', '42', 'private-application-key'),
    ]);
});

it('registers the Sentry redaction callbacks', function () {
    $sentryOptions = app(ClientBuilder::class)->getOptions();

    expect($sentryOptions->getBeforeSendCallback())->toBeInstanceOf(RedactSentryEvent::class)
        ->and($sentryOptions->getBeforeBreadcrumbCallback())
        ->toBeInstanceOf(RedactSentryBreadcrumb::class);
});
