<?php

arch('keeps HTTP, admin concerns and aborts out of queries')
    ->expect('App\Queries')
    ->not->toUse([
        'App\Http',
        'App\Filament',
        'Filament',
        'abort',
        'abort_if',
        'abort_unless',
    ]);
