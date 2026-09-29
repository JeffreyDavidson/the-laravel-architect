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

/** Minutes after midnight for an "H i" daily cron expression such as "30 2 * * *". */
function minutesAfterMidnight(string $expression): int
{
    [$minute, $hour] = array_map(intval(...), explode(' ', $expression));

    return $hour * 60 + $minute;
}

it('verifies the newest backup every day, after the backup has run', function () {
    $backup = scheduledEventFor('backup:run');
    $verification = scheduledEventFor('app:verify-backup');

    if (! $backup instanceof Event || ! $verification instanceof Event) {
        throw new RuntimeException('The backup and its verification must both be scheduled.');
    }

    expect($verification->expression)
        ->toBe('30 2 * * *')
        ->and(minutesAfterMidnight($verification->expression))
        ->toBeGreaterThan(minutesAfterMidnight($backup->expression));
});

it('runs the verification on one server at a time without overlapping the backup schedule', function () {
    $verification = scheduledEventFor('app:verify-backup');

    expect($verification?->onOneServer)
        ->toBeTrue()
        ->and($verification?->withoutOverlapping)
        ->toBeTrue();
});
