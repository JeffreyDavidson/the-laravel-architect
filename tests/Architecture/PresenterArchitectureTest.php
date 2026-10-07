<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Vite;

arch('keeps reads out of presenters')
    ->expect('App\Presenters')
    ->not->toUse([
        'App\Queries',
        DB::class,
    ]);

arch('injects presenter collaborators instead of reaching for facades')
    ->expect('App\Presenters')
    ->not->toUse([
        Storage::class,
        URL::class,
        Vite::class,
    ]);
