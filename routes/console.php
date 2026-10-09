<?php

use App\Enums\DeploymentEnvironment;
use App\Jobs\RecordQueueHeartbeat;
use App\Models\ContactInquiry;
use App\Models\Subscriber;
use Illuminate\Support\Facades\Schedule;
use JeffreyDavidson\CreatorKit\Services\Health\RuntimeHealthMonitor;
use Laravel\Nightwatch\Console\Sample;

/*
 * Backups, YouTube and media checks run only on the production deployment. Staging shares
 * production's small server and runs with APP_ENV=production, so `environments()` cannot
 * tell them apart; TLA_DEPLOYMENT_ENVIRONMENT (app.deployment_environment) can.
 */
$isProductionDeployment = fn (): bool => DeploymentEnvironment::current() === DeploymentEnvironment::Production;

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

/*
 * Each daily or weekly task's overlap lock expires after one or two hours instead of the
 * default 24, so a run killed mid-way (out of memory, a hung server) cannot leave a lock
 * that skips the next scheduled run.
 */
Schedule::command('backup:run')
    ->dailyAt(config('backup.schedule.run_at'))
    ->when($isProductionDeployment)
    ->withoutOverlapping(120)
    ->onOneServer();
// Restores the newest archive in isolation, so a verified backup exists before any deploy.
// It shares the backup's minute and is defined after it: a scheduler run executes due
// events one at a time in definition order (the backup must not run in the background),
// so this starts the moment the backup ends and a site write rarely lands in between.
// A failure is reported through the scheduler's failed-task email, like the other checks.
Schedule::command('app:verify-backup')
    ->dailyAt(config('backup.schedule.run_at'))
    ->when($isProductionDeployment)
    ->withoutOverlapping(120)
    ->onOneServer()
    ->emailOutputOnFailure(config('backup.notifications.mail.to'));
Schedule::command('backup:clean')
    ->weeklyOn(1, config('backup.schedule.clean_at'))
    ->when($isProductionDeployment)
    ->withoutOverlapping(60)
    ->onOneServer();
Schedule::command('backup:monitor')
    ->dailyAt(config('backup.schedule.monitor_at'))
    ->when($isProductionDeployment)
    ->withoutOverlapping(60)
    ->onOneServer();
Schedule::command('media:verify-responsive-images')
    ->dailyAt('05:00')
    ->when($isProductionDeployment)
    ->withoutOverlapping(60)
    ->onOneServer()
    ->emailOutputOnFailure(config('backup.notifications.mail.to'));
Schedule::command('media:find-orphans')
    ->weeklyOn(0, '05:30')
    ->when($isProductionDeployment)
    ->withoutOverlapping(60)
    ->onOneServer()
    ->emailOutputOnFailure(config('backup.notifications.mail.to'));
Schedule::command('queue:prune-failed', [
    '--hours' => config('health.failed_jobs.retention_hours'),
])
    ->daily()
    ->withoutOverlapping(60)
    ->onOneServer();
Schedule::command('model:prune', ['--model' => [ContactInquiry::class, Subscriber::class]])
    ->daily()
    ->withoutOverlapping(60)
    ->onOneServer();
Schedule::command('activitylog:clean')
    ->daily()
    ->withoutOverlapping(60)
    ->onOneServer();
// Off-peak, clear of the 02:00 backup and verification and the 05:00/05:30 media runs.
Schedule::command('cache:prune-expired')
    ->dailyAt('03:30')
    ->withoutOverlapping(60)
    ->onOneServer();
Schedule::command('youtube:stats')
    ->daily()
    ->when($isProductionDeployment)
    ->withoutOverlapping(60)
    ->onOneServer();
Schedule::command('youtube:sync')
    ->weekly()
    ->when($isProductionDeployment)
    ->withoutOverlapping(60)
    ->onOneServer();
