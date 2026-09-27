<?php

use Illuminate\Http\Client\StrayRequestException;
use Illuminate\Support\Facades\Http;

it('selects only the isolated test database', function () {
    $connection = config('database.default');
    $database = config('database.connections.sqlite.database');

    expect($connection)
        ->toBe('sqlite')
        ->and($database)
        ->toBe(':memory:');
});

it('rejects unfaked outgoing requests', function () {
    $exception = null;

    try {
        Http::get('https://unfaked.example.invalid');
    } catch (StrayRequestException $caught) {
        $exception = $caught;
    }

    expect(Http::preventingStrayRequests())
        ->toBeTrue()
        ->and($exception)
        ->toBeInstanceOf(StrayRequestException::class);
});

it('allows explicitly faked outgoing requests', function () {
    Http::fake(['example.invalid/*' => Http::response(['ok' => true])]);

    $response = Http::get('https://example.invalid/check');

    expect($response->json('ok'))
        ->toBeTrue();
    Http::assertSentCount(1);
});

it('never exposes real credentials to tests', function (string $key) {
    $isEmpty = blank(config($key));

    expect($isEmpty)
        ->toBeTrue();
})->with([
    'services.resend.key',
    'services.turnstile.site_key',
    'services.turnstile.secret_key',
    'services.youtube.api_key',
    'sentry.dsn',
    'nightwatch.token',
    'backup.backup.password',
    'filesystems.disks.b2-backups.key',
    'filesystems.disks.b2-backups.secret',
    'filesystems.disks.nas-backups.host',
    'filesystems.disks.nas-backups.username',
    'filesystems.disks.nas-backups.password',
    'filesystems.disks.s3.key',
    'filesystems.disks.s3.secret',
]);

it('uses non-delivering mail and local-only drivers', function (string $key, mixed $expected) {
    $value = config($key);

    expect($value)
        ->toBe($expected);
})->with([
    'mailer' => ['mail.default', 'array'],
    'cache' => ['cache.default', 'array'],
    'session' => ['session.driver', 'array'],
    'queue' => ['queue.default', 'sync'],
    'nightwatch' => ['nightwatch.enabled', false],
]);
