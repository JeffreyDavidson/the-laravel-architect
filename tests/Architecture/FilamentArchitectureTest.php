<?php

use App\Filament\Widgets\RecentActivityWidget;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

arch('keeps raw database access, caching and queued jobs out of the admin panel')
    ->expect('App\Filament')
    ->not->toUse([
        Cache::class,
        DB::class,
        'App\Jobs',
    ]);

arch('makes dashboard widgets read through Query classes')
    ->expect('App\Filament\Widgets')
    ->not->toUse([
        EloquentBuilder::class,
        QueryBuilder::class,
    ]);

/*
 * RecentActivityWidget names the models RecentlyEditedContentQuery returns to label and link
 * each row; it does not query them.
 */
arch('keeps Eloquent models out of dashboard widgets')
    ->expect('App\Filament\Widgets')
    ->not->toUse('App\Models')
    ->ignoring(RecentActivityWidget::class);
