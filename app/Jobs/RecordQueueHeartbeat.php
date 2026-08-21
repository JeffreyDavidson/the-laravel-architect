<?php

namespace App\Jobs;

use App\Support\RuntimeHealth;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class RecordQueueHeartbeat implements ShouldQueue
{
    use Queueable;

    public function handle(RuntimeHealth $runtimeHealth): void
    {
        $runtimeHealth->recordQueueHeartbeat();
    }
}
