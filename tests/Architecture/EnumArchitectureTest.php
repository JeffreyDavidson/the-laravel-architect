<?php

use Illuminate\Database\Eloquent\Builder;

arch('keeps queries, models and HTTP concerns out of enums')
    ->expect('App\Enums')
    ->not->toUse([
        'App\Models',
        'App\Services',
        'App\Queries',
        'App\Http',
        Builder::class,
    ]);
