<?php

namespace App\Monitoring;

use Laravel\Nightwatch\Records\OutgoingRequest;

final class RedactNightwatchOutgoingRequest
{
    public function __invoke(OutgoingRequest $request): bool
    {
        $urlWithoutQuery = explode('?', $request->url, 2)[0];

        $request->url = explode('#', $urlWithoutQuery, 2)[0];

        return true;
    }
}
