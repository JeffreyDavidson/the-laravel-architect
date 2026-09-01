<?php

namespace App\Monitoring;

use Laravel\Nightwatch\Records\Request;

final class RedactNightwatchRequest
{
    public function __invoke(Request $request): bool
    {
        $request->url = $request->routePath !== ''
            ? $request->routePath
            : '[unmatched route]';
        $request->ip = '';
        $request->headers->replace([]);

        return true;
    }
}
