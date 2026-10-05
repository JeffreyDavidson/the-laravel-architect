<?php

use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Artisan;
use Laravel\Nightwatch\Core;

/** The single scheduled event that runs the given artisan command. */
function scheduledCommandEvent(string $command): Event
{
    return collect(app(Schedule::class)->events())
        ->filter(fn (Event $event): bool => str_contains($event->command ?? '', " {$command}"))
        ->sole();
}

dataset('production-only tasks', [
    'backup:run' => ['backup:run', '0 2 * * *'],
    'app:verify-backup' => ['app:verify-backup', '30 2 * * *'],
    'backup:clean' => ['backup:clean', '0 3 * * 1'],
    'backup:monitor' => ['backup:monitor', '0 4 * * *'],
    'media:verify-responsive-images' => ['media:verify-responsive-images', '0 5 * * *'],
    'media:find-orphans' => ['media:find-orphans', '30 5 * * 0'],
    'youtube:stats' => ['youtube:stats', '0 0 * * *'],
    'youtube:sync' => ['youtube:sync', '0 0 * * 0'],
]);

dataset('tasks for every deployment', ['queue:prune-failed', 'model:prune', 'activitylog:clean', 'cache:prune-expired']);

it('skips production-only tasks on staging', function (string $command) {
    config(['app.deployment_environment' => 'staging']);

    $event = scheduledCommandEvent($command);

    expect($event->filtersPass(app()))->toBeFalse();
})->with('production-only tasks');

it('runs production-only tasks on production at their scheduled times', function (string $command, string $expression) {
    config(['app.deployment_environment' => 'production']);

    $event = scheduledCommandEvent($command);

    expect($event->filtersPass(app()))
        ->toBeTrue()
        ->and($event->expression)
        ->toBe($expression);
})->with('production-only tasks');

it('runs maintenance tasks on staging', function (string $command) {
    config(['app.deployment_environment' => 'staging']);

    $event = scheduledCommandEvent($command);

    expect($event->filtersPass(app()))->toBeTrue();
})->with('tasks for every deployment');

it('prunes expired cache entries daily between the backup and monitoring runs', function () {
    $event = scheduledCommandEvent('cache:prune-expired');

    expect($event->expression)
        ->toBe('30 3 * * *')
        ->and($event->withoutOverlapping)
        ->toBeTrue()
        ->and($event->onOneServer)
        ->toBeTrue();
});

it('schedules operational monitoring and maintenance', function () {
    Artisan::call('schedule:list');

    expect(Artisan::output())
        ->toContain('backup:run')
        ->toContain('backup:clean')
        ->toContain('backup:monitor')
        ->toContain('media:verify-responsive-images')
        ->toContain('media:find-orphans')
        ->toContain('queue:prune-failed --hours=168')
        ->toContain('activitylog:clean')
        ->toContain("--model='App\Models\Subscriber'")
        ->not
        ->toContain('app:monitor-failed-jobs');
});

it('samples runtime heartbeat traces at ten percent', function () {
    $schedule = app(Schedule::class);
    $events = $schedule->events();

    $heartbeat = collect($events)
        ->filter(function (Event $event): bool {
            $description = $event->description;

            return is_string($description)
                && str_starts_with($description, 'runtime-health:heartbeat:');
        })
        ->sole();

    $scheduledTasksSampleRates = new ReflectionProperty(Core::class, 'scheduledTasksSampleRates');
    $sampleRates = $scheduledTasksSampleRates->getValue(app(Core::class));

    /** @var WeakMap<Event, float> $sampleRates */
    expect($sampleRates[$heartbeat])->toBe(0.1);
});
