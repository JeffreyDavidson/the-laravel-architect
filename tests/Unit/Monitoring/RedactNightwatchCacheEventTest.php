<?php

use App\Monitoring\RedactNightwatchCacheEvent;
use Illuminate\Support\Facades\Config;
use Laravel\Nightwatch\Records\CacheEvent;

it('replaces cache keys with an application-keyed digest', function () {
    Config::set('app.key', 'private-application-key');
    $event = nightwatchCacheEvent('newsletter:203.0.113.10');

    $redacted = app(RedactNightwatchCacheEvent::class)($event);

    expect($redacted)->toBeTrue()
        ->and($event->key)->toBe(hash_hmac('sha256', 'newsletter:203.0.113.10', 'private-application-key'))
        ->not->toContain('newsletter', '203.0.113.10');
});

it('fully redacts cache keys when the application key is unavailable', function () {
    Config::set('app.key', null);
    $event = nightwatchCacheEvent('private-cache-key');

    app(RedactNightwatchCacheEvent::class)($event);

    expect($event->key)->toBe('[redacted]');
});

function nightwatchCacheEvent(string $key): CacheEvent
{
    return new CacheEvent(
        store: 'database',
        key: $key,
        type: 'hit',
        duration: 1,
        ttl: 60,
    );
}
