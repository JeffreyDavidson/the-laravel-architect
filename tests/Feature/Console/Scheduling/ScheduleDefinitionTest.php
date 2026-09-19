<?php

use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Artisan;
use Laravel\Nightwatch\Core;

it('schedules operational monitoring and maintenance', function () {
    Artisan::call('schedule:list');

    expect(Artisan::output())
        ->toContain('backup:run')
        ->toContain('backup:clean')
        ->toContain('backup:monitor')
        ->toContain('media:verify-responsive-images')
        ->toContain('media:find-orphans')
        ->toContain('queue:prune-failed --hours=168')
        ->not->toContain('app:monitor-failed-jobs');
});

it('samples runtime heartbeat traces at ten percent', function () {
    $heartbeat = collect(app(Schedule::class)->events())
        ->filter(fn (Event $event): bool => str_starts_with($event->description, 'runtime-health:heartbeat:'))
        ->sole();

    $sampleRates = new ReflectionProperty(Core::class, 'scheduledTasksSampleRates')
        ->getValue(app(Core::class));

    /** @var WeakMap<Event, float> $sampleRates */
    expect($sampleRates[$heartbeat])->toBe(0.1);
});
