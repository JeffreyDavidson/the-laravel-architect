<?php

use Illuminate\Support\Facades\Artisan;

it('uses safe backup configuration defaults', function () {
    expect(config('backup.backup.source.files.include'))
        ->not->toContain(base_path())
        ->not->toContain(null)
        ->and(config('backup.backup.source.files.exclude'))
        ->toContain(base_path('.env'))
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

it('configures a private S3-compatible disk for Backblaze backups', function () {
    expect(config('filesystems.disks.b2-backups'))
        ->toMatchArray([
            'driver' => 's3',
            'key' => null,
            'secret' => null,
            'region' => 'us-east-005',
            'bucket' => null,
            'endpoint' => null,
            'use_path_style_endpoint' => false,
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
        ->toContain('media:verify-responsive-images')
        ->toContain('queue:prune-failed --hours=168')
        ->not->toContain('app:monitor-failed-jobs');
});
