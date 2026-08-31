<?php

use App\Services\NightwatchHealthMonitor;
use JMac\Testing\Double;
use Laravel\Nightwatch\Contracts\Ingest;
use Laravel\Nightwatch\Core;

it('confirms the Nightwatch agent accepts connections', function () {
    $nightwatch = app(Core::class);
    $nightwatch->config['enabled'] = true;
    $ingest = Double::for(Ingest::class);
    $ingest->expects('ping');
    $nightwatch->ingest = $ingest;

    (new NightwatchHealthMonitor($nightwatch))->ensureHealthy();
});

it('rejects a disabled Nightwatch installation', function () {
    $nightwatch = app(Core::class);
    $nightwatch->config['enabled'] = false;

    expect(fn () => (new NightwatchHealthMonitor($nightwatch))->ensureHealthy())
        ->toThrow(RuntimeException::class, 'Nightwatch is disabled.');
});

it('does not expose an agent connection error', function () {
    $nightwatch = app(Core::class);
    $nightwatch->config['enabled'] = true;
    $ingest = Double::for(Ingest::class);
    $ingest->expects('ping')
        ->throws(new RuntimeException('private ingest address'));
    $nightwatch->ingest = $ingest;

    expect(fn () => (new NightwatchHealthMonitor($nightwatch))->ensureHealthy())
        ->toThrow(RuntimeException::class, 'The Nightwatch agent is unavailable.');
});
