<?php

namespace App\Support\Monitoring\Nightwatch;

use Laravel\Nightwatch\Core;
use Laravel\Nightwatch\Records\Request;
use Laravel\Nightwatch\State\CommandState;
use Laravel\Nightwatch\State\RequestState;

final readonly class RedactNightwatchRequest
{
    /** @param Core<RequestState|CommandState> $nightwatch */
    public function __construct(private Core $nightwatch) {}

    public function __invoke(Request $request): bool
    {
        $request->url = $request->routePath !== ''
            ? $request->routePath
            : '[unmatched route]';
        $this->nightwatch->executionState->executionPreview = "{$request->method} {$request->url}";
        $request->ip = '';
        $request->headers->replace([]);

        return true;
    }
}
