<?php

use App\Jobs\RecordQueueHeartbeat;
use App\Models\ContactInquiry;
use App\Models\Subscriber;
use App\Support\Monitoring\Health\RuntimeHealthMonitor;
use Illuminate\Support\Facades\Schedule;
use Laravel\Nightwatch\Console\Sample;

/*
 * Backups, YouTube and media checks run only on the production deployment. Staging shares
 * production's small server and runs with APP_ENV=production, so `environments()` cannot
 * tell them apart; TLA_DEPLOYMENT_ENVIRONMENT (app.deployment_environment) can.
 */
$isProductionDeployment = fn (): bool => config('app.deployment_environment') === 'production';

Schedule::call(function (RuntimeHealthMonitor $runtimeHealthMonitor): void {
    $runtimeHealthMonitor->recordSchedulerHeartbeat();
    RecordQueueHeartbeat::dispatch();
})
    ->name(
        'runtime-health:heartbeat:'.app()->environment(),
    )
    ->everyMinute()
    ->tap(Sample::rate(0.1))
    ->withoutOverlapping(5)
    ->onOneServer();

Schedule::command('backup:run')
    ->dailyAt(config('backup.schedule.run_at'))
    ->when($isProductionDeployment)
    ->withoutOverlapping()
    ->onOneServer();
// Restores the newest archive in isolation, so a verified backup exists before any deploy.
// A failure is reported through the scheduler's failed-task email, like the other checks.
Schedule::command('app:verify-backup')
    ->dailyAt(config('backup.schedule.verify_at'))
    ->when($isProductionDeployment)
    ->withoutOverlapping()
    ->onOneServer()
    ->emailOutputOnFailure(config('backup.notifications.mail.to'));
Schedule::command('backup:clean')
    ->weeklyOn(1, config('backup.schedule.clean_at'))
    ->when($isProductionDeployment)
    ->withoutOverlapping()
    ->onOneServer();
Schedule::command('backup:monitor')
    ->dailyAt(config('backup.schedule.monitor_at'))
    ->when($isProductionDeployment)
    ->withoutOverlapping()
    ->onOneServer();
Schedule::command('media:verify-responsive-images')
    ->dailyAt('05:00')
    ->when($isProductionDeployment)
    ->withoutOverlapping()
    ->onOneServer()
    ->emailOutputOnFailure(config('backup.notifications.mail.to'));
Schedule::command('media:find-orphans')
    ->weeklyOn(0, '05:30')
    ->when($isProductionDeployment)
    ->withoutOverlapping()
    ->onOneServer()
    ->emailOutputOnFailure(config('backup.notifications.mail.to'));
Schedule::command('queue:prune-failed', [
    '--hours' => config('health.failed_jobs.retention_hours'),
])
    ->daily()
    ->withoutOverlapping()
    ->onOneServer();
Schedule::command('model:prune', ['--model' => [ContactInquiry::class, Subscriber::class]])
    ->daily()
    ->withoutOverlapping()
    ->onOneServer();
Schedule::command('activitylog:clean')
    ->daily()
    ->withoutOverlapping()
    ->onOneServer();
// Off-peak, clear of the 02:00/02:30 backup and 05:00/05:30 media runs.
Schedule::command('cache:prune-expired')
    ->dailyAt('03:30')
    ->withoutOverlapping()
    ->onOneServer();
Schedule::command('youtube:stats')
    ->daily()
    ->when($isProductionDeployment)
    ->withoutOverlapping()
    ->onOneServer();
Schedule::command('youtube:sync')
    ->weekly()
    ->when($isProductionDeployment)
    ->withoutOverlapping()
    ->onOneServer();
