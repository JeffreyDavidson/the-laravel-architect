<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Services\Health\RuntimeHealthMonitor;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Attributes\Backoff;
use Illuminate\Queue\Attributes\Tries;

#[Tries(3)]
#[Backoff([5, 15, 30])]
final class RecordQueueHeartbeat implements ShouldQueue
{
    use Queueable;

    public function handle(RuntimeHealthMonitor $runtimeHealthMonitor): void
    {
        $runtimeHealthMonitor->recordQueueHeartbeat();
    }
}
