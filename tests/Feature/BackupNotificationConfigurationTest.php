<?php

use Spatie\Backup\Notifications\Notifications\BackupHasFailedNotification;
use Spatie\Backup\Notifications\Notifications\BackupWasSuccessfulNotification;
use Spatie\Backup\Notifications\Notifications\CleanupHasFailedNotification;
use Spatie\Backup\Notifications\Notifications\CleanupWasSuccessfulNotification;
use Spatie\Backup\Notifications\Notifications\HealthyBackupWasFoundNotification;
use Spatie\Backup\Notifications\Notifications\UnhealthyBackupWasFoundNotification;

it('emails only when backup attention is required', function (): void {
    $notifications = config('backup.notifications.notifications');

    expect($notifications)
        ->toHaveKeys([
            BackupHasFailedNotification::class,
            CleanupHasFailedNotification::class,
            UnhealthyBackupWasFoundNotification::class,
        ])
        ->not->toHaveKeys([
            BackupWasSuccessfulNotification::class,
            CleanupWasSuccessfulNotification::class,
            HealthyBackupWasFoundNotification::class,
        ]);
});
