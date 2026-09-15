<?php

use App\Jobs\RecordQueueHeartbeat;
use App\Models\ContactInquiry;
use App\Support\Monitoring\Health\RuntimeHealthMonitor;
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
Schedule::command('media:verify-responsive-images')
    ->dailyAt('05:00')
    ->environments(['production'])
    ->withoutOverlapping()
    ->onOneServer()
    ->emailOutputOnFailure(config('backup.notifications.mail.to'));
Schedule::command('media:find-orphans')
    ->weeklyOn(0, '05:30')
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
Schedule::command('model:prune', ['--model' => ContactInquiry::class])
    ->daily()
    ->withoutOverlapping()
    ->onOneServer();
Schedule::command('youtube:stats')->daily()->withoutOverlapping()->onOneServer();
Schedule::command('youtube:sync')->weekly()->withoutOverlapping()->onOneServer();
