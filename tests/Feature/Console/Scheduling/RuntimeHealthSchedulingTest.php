<?php

use App\Jobs\RecordQueueHeartbeat;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use JeffreyDavidson\CreatorKit\Services\Health\RuntimeHealthMonitor;

use function Pest\Laravel\travelTo;

beforeEach(function () {
    Cache::flush();
});

it('schedules a heartbeat that records the scheduler and probes the queue', function () {
    Queue::fake();
    travelTo('2026-08-21 12:34:00');

    Artisan::call('schedule:run');

    expect(Cache::get(RuntimeHealthMonitor::SCHEDULER_HEARTBEAT_KEY))->toBe(now()->getTimestamp());
    Queue::assertPushed(RecordQueueHeartbeat::class);
});
