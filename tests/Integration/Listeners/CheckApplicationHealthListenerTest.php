<?php

use App\Listeners\CheckApplicationHealthListener;
use Illuminate\Foundation\Events\DiagnosingHealth;
use Illuminate\Support\Facades\Event;

it('runs the health checks once per health request', function () {
    $listeners = Event::getRawListeners()[DiagnosingHealth::class] ?? [];

    expect($listeners)
        ->toBe([CheckApplicationHealthListener::class.'@handle']);
});
