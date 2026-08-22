<?php

namespace App\Console\Commands;

use App\Notifications\BackupDeliveryTest;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

#[Signature('app:test-backup-notification')]
#[Description('Send a test message through the configured backup notification mail channel')]
class SendBackupNotificationTest extends Command
{
    public function handle(): int
    {
        $recipient = config('backup.notifications.mail.to');

        if (! is_string($recipient) || filter_var($recipient, FILTER_VALIDATE_EMAIL) === false) {
            $this->error('The backup notification recipient is not configured.');

            return self::FAILURE;
        }

        Notification::route('mail', $recipient)
            ->notify(new BackupDeliveryTest);

        $this->info('Backup notification delivery test sent.');

        return self::SUCCESS;
    }
}
