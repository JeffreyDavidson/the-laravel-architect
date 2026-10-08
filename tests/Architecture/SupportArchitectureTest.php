<?php

use App\Support\Seo\StructuredDataBuilder;
use Illuminate\Http\Request;

arch('keeps models out of the feed renderers')
    ->expect('App\Support\Feeds')
    ->not->toUse('App\Models');

/*
 * StructuredDataBuilder still reads the current route from the request. It is ignored until the
 * page ViewModels build their own structured data and the builder leaves app/Support.
 */
arch('keeps HTTP aborts and the request out of support classes')
    ->expect('App\Support')
    ->not->toUse([
        Request::class,
        'abort',
        'abort_if',
        'abort_unless',
    ])
    ->ignoring(StructuredDataBuilder::class);
