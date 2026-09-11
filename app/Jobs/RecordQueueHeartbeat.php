<?php

namespace App\Jobs;

use App\Support\Monitoring\Health\RuntimeHealthMonitor;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class RecordQueueHeartbeat implements ShouldQueue
{
    use Queueable;

    public function handle(RuntimeHealthMonitor $runtimeHealthMonitor): void
    {
        $runtimeHealthMonitor->recordQueueHeartbeat();
    }
}
