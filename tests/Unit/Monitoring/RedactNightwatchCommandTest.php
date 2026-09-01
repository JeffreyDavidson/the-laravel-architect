<?php

use App\Monitoring\RedactNightwatchCommand;
use Laravel\Nightwatch\Records\Command;

it('removes arguments and options from command telemetry', function () {
    $command = new Command(
        class: 'App\\Console\\Commands\\ExampleCommand',
        name: 'app:example',
        command: 'artisan app:example private@example.test --token=private-token',
        exitCode: 0,
        duration: 1,
    );

    $redacted = app(RedactNightwatchCommand::class)($command);

    expect($redacted)->toBeTrue()
        ->and($command->command)->toBe('app:example')
        ->not->toContain('private@example.test', 'private-token');
});
