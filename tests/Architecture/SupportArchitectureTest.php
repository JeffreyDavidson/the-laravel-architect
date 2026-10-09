<?php

use Illuminate\Http\Request;

arch('keeps support classes free of the application layers, the request and HTTP aborts')
    ->expect('App\Support')
    ->not->toUse([
        'App\Models',
        'App\Presenters',
        'App\ViewModels',
        'App\Http',
        'App\Filament',
        Request::class,
        'abort',
        'abort_if',
        'abort_unless',
    ]);
