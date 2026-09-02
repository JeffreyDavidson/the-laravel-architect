<?php

use Illuminate\Support\Facades\Artisan;

it('uses safe backup configuration defaults', function () {
    expect(config('backup.backup.source.files.include'))
        ->toContain(base_path())
        ->not->toContain(null)
        ->and(config('backup.backup.destination.disks'))->toBe(['local'])
        ->and(config('backup.monitor_backups.0.disks'))->toBe(['local'])
        ->and(config('backup.notifications.mail.to'))->toBe(config('mail.contact_to'));
});

it('configures a private SFTP disk for NAS backups', function () {
    expect(config('filesystems.disks.nas-backups'))
        ->toMatchArray([
            'driver' => 'sftp',
            'host' => null,
            'username' => null,
            'password' => null,
            'port' => 22,
            'root' => '/laravel-backups',
            'hostFingerprint' => null,
            'timeout' => 30,
            'maxTries' => 3,
            'visibility' => 'private',
            'directory_visibility' => 'private',
            'throw' => true,
            'report' => true,
        ]);
});

it('schedules operational monitoring and maintenance', function () {
    Artisan::call('schedule:list');

    expect(Artisan::output())
        ->toContain('backup:run')
        ->toContain('backup:clean')
        ->toContain('backup:monitor')
        ->toContain('app:monitor-failed-jobs')
        ->toContain('media:verify-responsive-images')
        ->toContain('queue:prune-failed --hours=168');
});
