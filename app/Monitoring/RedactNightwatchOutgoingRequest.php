<?php

namespace App\Monitoring;

use Laravel\Nightwatch\Records\OutgoingRequest;

final class RedactNightwatchOutgoingRequest
{
    public function __invoke(OutgoingRequest $request): bool
    {
        $scheme = parse_url($request->url, PHP_URL_SCHEME);
        $host = parse_url($request->url, PHP_URL_HOST);

        $request->url = is_string($scheme)
            && in_array($scheme, ['http', 'https'], true)
            && is_string($host)
            && $host !== ''
                ? "{$scheme}://{$host}"
                : '[redacted]';

        return true;
    }
}
