<?php

use App\Jobs\RecordQueueHeartbeat;
use App\Services\Health\RuntimeHealthMonitor;
use Illuminate\Queue\Queue;
use Illuminate\Support\Facades\Cache;

use function Pest\Laravel\travelTo;

beforeEach(function () {
    Cache::flush();
});

it('records a queue heartbeat when the job is processed', function () {
    travelTo('2026-08-21 12:34:00');

    app(RecordQueueHeartbeat::class)
        ->handle(app(RuntimeHealthMonitor::class));

    expect(Cache::get(RuntimeHealthMonitor::QUEUE_HEARTBEAT_KEY))->toBe(now()->getTimestamp());
});

it('retries after transient queue storage contention', function () {
    $job = new RecordQueueHeartbeat;
    $queue = app('queue')->connection();

    if (! $queue instanceof Queue) {
        throw new UnexpectedValueException('The default queue connection does not read job retry settings.');
    }

    expect($queue->getJobTries($job))
        ->toBe(3)
        ->and($queue->getJobBackoff($job))
        ->toBe('5,15,30');
});
