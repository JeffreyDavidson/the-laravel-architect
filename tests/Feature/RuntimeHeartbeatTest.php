<?php

use App\Jobs\RecordQueueHeartbeat;
use App\Services\RuntimeHealthMonitor;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    Cache::flush();
});

it('schedules a heartbeat that records the scheduler and probes the queue', function () {
    Queue::fake();
    $this->travelTo('2026-08-21 12:34:00');

    Artisan::call('schedule:run');

    expect(Cache::get(RuntimeHealthMonitor::SCHEDULER_HEARTBEAT_KEY))->toBe(now()->getTimestamp());
    Queue::assertPushed(RecordQueueHeartbeat::class);
});

it('records a heartbeat when the queued probe is processed', function () {
    $this->travelTo('2026-08-21 12:34:00');

    app(RecordQueueHeartbeat::class)
        ->handle(app(RuntimeHealthMonitor::class));

    expect(Cache::get(RuntimeHealthMonitor::QUEUE_HEARTBEAT_KEY))->toBe(now()->getTimestamp());
});
