<?php

use App\Jobs\RecordQueueHeartbeat;
use App\Support\Monitoring\Health\RuntimeHealthMonitor;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    Cache::flush();
});

it('records a queue heartbeat when the job is processed', function () {
    $this->travelTo('2026-08-21 12:34:00');

    app(RecordQueueHeartbeat::class)
        ->handle(app(RuntimeHealthMonitor::class));

    expect(Cache::get(RuntimeHealthMonitor::QUEUE_HEARTBEAT_KEY))->toBe(now()->getTimestamp());
});
