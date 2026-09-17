<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Support\Monitoring\Health\RuntimeHealthMonitor;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class RecordQueueHeartbeat implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    /** @var list<int> */
    public array $backoff = [5, 15, 30];

    public function handle(RuntimeHealthMonitor $runtimeHealthMonitor): void
    {
        $runtimeHealthMonitor->recordQueueHeartbeat();
    }
}
