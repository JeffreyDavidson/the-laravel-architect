<?php

arch('keeps models independent of the publishing rules')
    ->expect('App\Models')
    ->not->toUse('App\Publishing');

arch('keeps the publishing rules free of admin and HTTP concerns')
    ->expect('App\Publishing')
    ->not->toUse([
        'App\Filament',
        'App\Http',
        'App\Livewire',
        'Filament',
        'Livewire',
    ]);
