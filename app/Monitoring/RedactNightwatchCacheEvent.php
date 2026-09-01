<?php

namespace App\Monitoring;

use Illuminate\Support\Facades\Config;
use Laravel\Nightwatch\Records\CacheEvent;

final class RedactNightwatchCacheEvent
{
    public function __invoke(CacheEvent $event): bool
    {
        $appKey = Config::get('app.key');

        $event->key = is_string($appKey) && $appKey !== ''
            ? hash_hmac('sha256', $event->key, $appKey)
            : '[redacted]';

        return true;
    }
}
