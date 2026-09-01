<?php

namespace App\Monitoring;

use Laravel\Nightwatch\Records\Command;

final class RedactNightwatchCommand
{
    public function __invoke(Command $command): bool
    {
        $command->command = $command->name;

        return true;
    }
}
