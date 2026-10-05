<?php

use Illuminate\Support\Facades\Route;

it('does not serve the private local disk over HTTP', function () {
    expect(Route::has('storage.local'))
        ->toBeFalse()
        ->and(Route::has('storage.local.upload'))
        ->toBeFalse();
});
