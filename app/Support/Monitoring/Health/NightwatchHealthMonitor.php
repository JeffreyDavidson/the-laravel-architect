<?php

declare(strict_types=1);

namespace App\Support\Monitoring\Health;

use Laravel\Nightwatch\Core;
use Laravel\Nightwatch\State\CommandState;
use Laravel\Nightwatch\State\RequestState;
use RuntimeException;
use Throwable;

class NightwatchHealthMonitor
{
    /**
     * @param  Core<RequestState|CommandState>  $nightwatch
     */
    public function __construct(private readonly Core $nightwatch) {}

    public function ensureHealthy(): void
    {
        if (! $this->nightwatch->enabled()) {
            throw new RuntimeException('Nightwatch is disabled.');
        }

        try {
            $this->nightwatch->ingest->ping();
        } catch (Throwable $exception) {
            throw new RuntimeException('The Nightwatch agent is unavailable.', $exception->getCode(), previous: $exception);
        }
    }
}
