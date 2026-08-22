<?php

use App\Notifications\BackupDeliveryTest;
use Illuminate\Support\Facades\Notification;

it('sends an on-demand backup notification delivery test', function () {
    Notification::fake();
    config()->set('backup.notifications.mail.to', 'backups@example.test');

    $this->artisan('app:test-backup-notification')
        ->expectsOutput('Backup notification delivery test sent.')
        ->assertSuccessful();

    Notification::assertSentOnDemand(BackupDeliveryTest::class);
});
