<?php

use App\Listeners\CheckApplicationHealthListener;
use Illuminate\Foundation\Events\DiagnosingHealth;
use Illuminate\Support\Facades\Event;

// The checks themselves run through /up in tests/Feature/Http/Controllers/HealthEndpointTest.php.
it('is registered exactly once for the health event', function () {
    $listeners = Event::getRawListeners()[DiagnosingHealth::class] ?? [];

    expect($listeners)
        ->toBe([CheckApplicationHealthListener::class.'@handle']);
});
