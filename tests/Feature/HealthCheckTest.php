<?php

use App\Support\RuntimeHealth;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

it('reports that the application and database are healthy', function () {
    $this->getJson('/up')
        ->assertOk()
        ->assertExactJson(['status' => 'up']);
});

it('reports an unavailable database as unhealthy', function () {
    $defaultConnection = config('database.default');

    config()->set([
        'app.debug' => false,
        'database.default' => 'unavailable',
        'database.connections.unavailable' => [
            'driver' => 'sqlite',
            'database' => '/dev/null/database.sqlite',
            'prefix' => '',
            'foreign_key_constraints' => true,
        ],
    ]);
    DB::purge('unavailable');

    $response = $this->getJson('/up');

    DB::purge('unavailable');
    config()->set('database.default', $defaultConnection);

    $response
        ->assertStatus(500)
        ->assertExactJson(['status' => 'down']);
});

it('reports fresh scheduler and queue heartbeats as healthy when runtime monitoring is enabled', function () {
    config()->set('health.runtime.enabled', true);
    Cache::put(RuntimeHealth::SCHEDULER_HEARTBEAT_KEY, now()->getTimestamp());
    Cache::put(RuntimeHealth::QUEUE_HEARTBEAT_KEY, now()->getTimestamp());

    $this->getJson('/up')
        ->assertOk()
        ->assertExactJson(['status' => 'up']);
});

it('reports a stale scheduler heartbeat as unhealthy', function () {
    config()->set([
        'app.debug' => false,
        'health.runtime.enabled' => true,
    ]);
    Cache::put(RuntimeHealth::SCHEDULER_HEARTBEAT_KEY, now()->subMinutes(6)->getTimestamp());
    Cache::put(RuntimeHealth::QUEUE_HEARTBEAT_KEY, now()->getTimestamp());

    $this->getJson('/up')
        ->assertStatus(500)
        ->assertExactJson(['status' => 'down']);
});

it('reports a stale queue worker heartbeat as unhealthy', function () {
    config()->set([
        'app.debug' => false,
        'health.runtime.enabled' => true,
    ]);
    Cache::put(RuntimeHealth::SCHEDULER_HEARTBEAT_KEY, now()->getTimestamp());
    Cache::put(RuntimeHealth::QUEUE_HEARTBEAT_KEY, now()->subMinutes(6)->getTimestamp());

    $this->getJson('/up')
        ->assertStatus(500)
        ->assertExactJson(['status' => 'down']);
});

it('reports invalid runtime heartbeat configuration as unhealthy', function () {
    config()->set([
        'app.debug' => false,
        'health.runtime.enabled' => true,
        'health.runtime.max_age_seconds' => 30,
    ]);
    Cache::put(RuntimeHealth::SCHEDULER_HEARTBEAT_KEY, now()->getTimestamp());
    Cache::put(RuntimeHealth::QUEUE_HEARTBEAT_KEY, now()->getTimestamp());

    $this->getJson('/up')
        ->assertStatus(500)
        ->assertExactJson(['status' => 'down']);
});
