<?php

use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;

/** The scheduled event that runs the given artisan command, if any. */
function scheduledEventFor(string $command): ?Event
{
    foreach (app(Schedule::class)->events() as $event) {
        if (str_ends_with($event->command ?? '', $command)) {
            return $event;
        }
    }

    return null;
}

/** The position of the given artisan command in the schedule, which is the order a scheduler run executes due events in. */
function scheduledEventPosition(string $command): int
{
    foreach (array_values(app(Schedule::class)->events()) as $position => $event) {
        if (str_ends_with($event->command ?? '', $command)) {
            return $position;
        }
    }

    throw new RuntimeException("[{$command}] is not scheduled.");
}

it('verifies the newest backup in the same scheduler run, straight after the backup finishes', function () {
    $backup = scheduledEventFor('backup:run');
    $verification = scheduledEventFor('app:verify-backup');

    if (! $backup instanceof Event || ! $verification instanceof Event) {
        throw new RuntimeException('The backup and its verification must both be scheduled.');
    }

    expect($verification->expression)
        ->toBe('0 2 * * *')
        ->toBe($backup->expression)
        ->and(scheduledEventPosition('app:verify-backup'))
        ->toBeGreaterThan(scheduledEventPosition('backup:run'))
        ->and($backup->runInBackground)
        ->toBeFalse();
});

it('runs the verification on one server at a time without overlapping the backup schedule', function () {
    $verification = scheduledEventFor('app:verify-backup');

    expect($verification?->onOneServer)
        ->toBeTrue()
        ->and($verification?->withoutOverlapping)
        ->toBeTrue();
});
