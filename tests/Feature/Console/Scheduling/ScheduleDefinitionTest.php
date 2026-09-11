<?php

use Illuminate\Support\Facades\Artisan;

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
