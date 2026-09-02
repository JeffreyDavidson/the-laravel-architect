<?php

namespace App\Monitoring;

use Laravel\Nightwatch\Core;
use Laravel\Nightwatch\Records\Exception;
use Laravel\Nightwatch\State\CommandState;
use Laravel\Nightwatch\State\RequestState;

final class RedactNightwatchException
{
    /** @param Core<RequestState|CommandState> $nightwatch */
    public function __construct(private readonly Core $nightwatch) {}

    public function __invoke(Exception $exception): bool
    {
        $exception->message = 'Exception message redacted.';

        if (! $exception->handled) {
            $this->nightwatch->executionState->exceptionPreview = $exception->message;
        }

        return true;
    }
}
