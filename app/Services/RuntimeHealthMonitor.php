<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use RuntimeException;

class RuntimeHealthMonitor
{
    public const string QUEUE_HEARTBEAT_KEY = 'health.runtime.queue.last_seen_at';

    public const string SCHEDULER_HEARTBEAT_KEY = 'health.runtime.scheduler.last_seen_at';

    public function recordSchedulerHeartbeat(): void
    {
        Cache::forever(self::SCHEDULER_HEARTBEAT_KEY, now()->getTimestamp());
    }

    public function recordQueueHeartbeat(): void
    {
        Cache::forever(self::QUEUE_HEARTBEAT_KEY, now()->getTimestamp());
    }

    public function ensureHealthy(): void
    {
        $maxAge = config('health.runtime.max_age_seconds');

        if (! is_int($maxAge) || $maxAge < 60) {
            throw new RuntimeException('Runtime heartbeat maximum age is invalid.');
        }

        $currentTimestamp = now()->getTimestamp();
        $oldestHealthyTimestamp = $currentTimestamp - $maxAge;

        if (! $this->isFresh(Cache::get(self::SCHEDULER_HEARTBEAT_KEY), $oldestHealthyTimestamp, $currentTimestamp)) {
            throw new RuntimeException('The scheduler heartbeat is stale.');
        }

        if (! $this->isFresh(Cache::get(self::QUEUE_HEARTBEAT_KEY), $oldestHealthyTimestamp, $currentTimestamp)) {
            throw new RuntimeException('The queue worker heartbeat is stale.');
        }
    }

    private function isFresh(mixed $timestamp, int $oldestHealthyTimestamp, int $currentTimestamp): bool
    {
        return is_int($timestamp)
            && $timestamp >= $oldestHealthyTimestamp
            && $timestamp <= $currentTimestamp;
    }
}
