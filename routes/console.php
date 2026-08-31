<?php

use App\Jobs\RecordQueueHeartbeat;
use App\Services\RuntimeHealthMonitor;
use Illuminate\Support\Facades\Schedule;

Schedule::call(function (RuntimeHealthMonitor $runtimeHealthMonitor): void {
    $runtimeHealthMonitor->recordSchedulerHeartbeat();
    RecordQueueHeartbeat::dispatch();
})
    ->name('runtime-health:heartbeat')
    ->everyMinute()
    ->withoutOverlapping(5)
    ->onOneServer();

Schedule::command('backup:run')
    ->dailyAt(config('backup.schedule.run_at'))
    ->withoutOverlapping()
    ->onOneServer();
Schedule::command('backup:clean')
    ->weeklyOn(1, config('backup.schedule.clean_at'))
    ->withoutOverlapping()
    ->onOneServer();
Schedule::command('backup:monitor')
    ->dailyAt(config('backup.schedule.monitor_at'))
    ->withoutOverlapping()
    ->onOneServer();
Schedule::command('app:monitor-failed-jobs')
    ->hourly()
    ->environments(['production'])
    ->withoutOverlapping()
    ->onOneServer()
    ->emailOutputOnFailure(config('backup.notifications.mail.to'));
Schedule::command('media:verify-responsive-images')
    ->dailyAt('05:00')
    ->environments(['production'])
    ->withoutOverlapping()
    ->onOneServer()
    ->emailOutputOnFailure(config('backup.notifications.mail.to'));
Schedule::command('queue:prune-failed', [
    '--hours' => config('health.failed_jobs.retention_hours'),
])
    ->daily()
    ->withoutOverlapping()
    ->onOneServer();
Schedule::command('youtube:stats')->daily()->withoutOverlapping()->onOneServer();
Schedule::command('youtube:sync')->weekly()->withoutOverlapping()->onOneServer();
