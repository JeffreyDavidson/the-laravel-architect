<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Queue\Failed\CountableFailedJobProvider;
use Illuminate\Queue\Failed\FailedJobProviderInterface;

#[Signature('app:monitor-failed-jobs')]
#[Description('Fail when the number of retained failed jobs exceeds the configured threshold')]
class MonitorFailedJobs extends Command
{
    public function __construct(private readonly FailedJobProviderInterface $failedJobs)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $threshold = config('health.failed_jobs.alert_threshold');

        if (! is_int($threshold) || $threshold < 0) {
            $this->error('Failed-job alert threshold is invalid.');

            return self::FAILURE;
        }

        $count = $this->failedJobs instanceof CountableFailedJobProvider
            ? $this->failedJobs->count()
            : count($this->failedJobs->all());

        if ($count > $threshold) {
            $this->error("Failed jobs exceed the configured threshold: {$count} retained.");

            return self::FAILURE;
        }

        $this->info("Failed-job count is healthy: {$count} retained.");

        return self::SUCCESS;
    }
}
