<?php

arch('keeps HTTP, URLs, admin concerns and aborts out of queries')
    ->expect('App\Queries')
    ->not->toUse([
        'App\Http',
        'App\Filament',
        'Filament',
        'Illuminate\Http',
        'abort',
        'abort_if',
        'abort_unless',
        'request',
        'route',
        'url',
    ]);
