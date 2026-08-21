<?php

use App\Jobs\RecordQueueHeartbeat;
use App\Support\RuntimeHealth;
use Illuminate\Support\Facades\Schedule;

Schedule::call(function (RuntimeHealth $runtimeHealth): void {
    $runtimeHealth->recordSchedulerHeartbeat();
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
Schedule::command('youtube:stats')->daily()->withoutOverlapping()->onOneServer();
Schedule::command('youtube:sync')->weekly()->withoutOverlapping()->onOneServer();
