<?php

declare(strict_types=1);

use App\Services\Health\RuntimeHealthMonitor;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Date;

use function Pest\Laravel\travelTo;

beforeEach(function (): void {
    travelTo('2026-10-08 12:00:00');
});

it('records both heartbeats as the current timestamp under the keys the apps already use', function (): void {
    $monitor = app(RuntimeHealthMonitor::class);

    $monitor->recordSchedulerHeartbeat();
    $monitor->recordQueueHeartbeat();

    expect(Cache::get('health.runtime.scheduler.last_seen_at'))->toBe(Date::now()->getTimestamp())
        ->and(Cache::get('health.runtime.queue.last_seen_at'))
        ->toBe(Date::now()->getTimestamp());
});

it('passes when both heartbeats are within the maximum age', function (): void {
    Cache::forever(RuntimeHealthMonitor::SCHEDULER_HEARTBEAT_KEY, 1791460500);
    Cache::forever(RuntimeHealthMonitor::QUEUE_HEARTBEAT_KEY, Date::now()->getTimestamp());

    app(RuntimeHealthMonitor::class)->ensureHealthy();
})->throwsNoExceptions();

it('fails when a heartbeat is missing, stale, in the future or not a timestamp', function (string $key, mixed $value, string $message): void {
    Cache::forever(RuntimeHealthMonitor::SCHEDULER_HEARTBEAT_KEY, Date::now()->getTimestamp());
    Cache::forever(RuntimeHealthMonitor::QUEUE_HEARTBEAT_KEY, Date::now()->getTimestamp());
    Cache::forever($key, $value);

    expect(fn () => app(RuntimeHealthMonitor::class)->ensureHealthy())
        ->toThrow(RuntimeException::class, $message);
})->with([
    'missing scheduler' => [RuntimeHealthMonitor::SCHEDULER_HEARTBEAT_KEY, null, 'The scheduler heartbeat is stale.'],
    'stale scheduler' => [RuntimeHealthMonitor::SCHEDULER_HEARTBEAT_KEY, 1791460499, 'The scheduler heartbeat is stale.'],
    'future scheduler' => [RuntimeHealthMonitor::SCHEDULER_HEARTBEAT_KEY, 1791460801, 'The scheduler heartbeat is stale.'],
    'string scheduler' => [RuntimeHealthMonitor::SCHEDULER_HEARTBEAT_KEY, '1791460800', 'The scheduler heartbeat is stale.'],
    'missing queue' => [RuntimeHealthMonitor::QUEUE_HEARTBEAT_KEY, null, 'The queue worker heartbeat is stale.'],
    'stale queue' => [RuntimeHealthMonitor::QUEUE_HEARTBEAT_KEY, 1791460499, 'The queue worker heartbeat is stale.'],
]);

it('reads the maximum age from config', function (): void {
    Config::set('health.runtime.max_age_seconds', 600);
    Cache::forever(RuntimeHealthMonitor::SCHEDULER_HEARTBEAT_KEY, 1791460200);
    Cache::forever(RuntimeHealthMonitor::QUEUE_HEARTBEAT_KEY, 1791460200);

    app(RuntimeHealthMonitor::class)->ensureHealthy();
})->throwsNoExceptions();

it('defaults the maximum age to 300 seconds', function (): void {
    expect(Config::get('health.runtime.max_age_seconds'))->toBe(300);
});

it('refuses a maximum age that is not an integer of at least 60 seconds', function (mixed $maxAge): void {
    Config::set('health.runtime.max_age_seconds', $maxAge);

    expect(fn () => app(RuntimeHealthMonitor::class)->ensureHealthy())
        ->toThrow(RuntimeException::class, 'Runtime heartbeat maximum age is invalid.');
})->with([
    'too short' => [59],
    'string' => ['300'],
    'missing' => [null],
]);
