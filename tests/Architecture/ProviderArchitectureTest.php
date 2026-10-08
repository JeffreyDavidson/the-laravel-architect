<?php

arch('keeps Filament configuration in the admin panel provider')
    ->expect('App\Providers')
    ->not->toUse('Filament')
    ->ignoring('App\Providers\Filament');

arch('names event listeners for the action they take')
    ->expect('App\Listeners')
    ->toHaveSuffix('Listener');
