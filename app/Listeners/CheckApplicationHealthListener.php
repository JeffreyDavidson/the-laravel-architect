<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Services\Health\RuntimeHealthMonitor;
use Illuminate\Foundation\Events\DiagnosingHealth;
use Illuminate\Support\Facades\DB;

/**
 * Runs the checks behind the `/up` health route. Any exception thrown here
 * makes the route report the application as down. Laravel discovers this
 * listener from its type-hinted event, so it is not registered by hand.
 */
final readonly class CheckApplicationHealthListener
{
    public function __construct(private RuntimeHealthMonitor $runtimeHealthMonitor) {}

    public function handle(DiagnosingHealth $_event): void
    {
        DB::table('migrations')
            ->limit(1)
            ->exists();

        if (config()->boolean('health.runtime.enabled')) {
            $this->runtimeHealthMonitor->ensureHealthy();
        }
    }
}
