<?php

use Illuminate\Support\Facades\Artisan;

it('uses safe backup configuration defaults', function () {
    expect(config('backup.backup.source.files.include'))
        ->toContain(base_path())
        ->not->toContain(null)
        ->and(config('backup.backup.destination.disks'))->toBe(['local'])
        ->and(config('backup.monitor_backups.0.disks'))->toBe(['local'])
        ->and(config('backup.notifications.mail.to'))->toBe('thelaravelarchitect@gmail.com');
});

it('schedules backup creation cleanup and monitoring', function () {
    Artisan::call('schedule:list');

    expect(Artisan::output())
        ->toContain('backup:run')
        ->toContain('backup:clean')
        ->toContain('backup:monitor');
});
