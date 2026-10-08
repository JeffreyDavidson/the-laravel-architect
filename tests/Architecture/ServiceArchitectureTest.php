<?php

use App\Services\PublicPageBenchmark;
use Illuminate\Http\Request;

/*
 * Services take the values they need (a token, a client IP) from their caller instead of reading
 * the current HTTP request. PublicPageBenchmark builds its own requests to send through the kernel.
 */
arch('keeps the current HTTP request out of services')
    ->expect('App\Services')
    ->not->toUse(Request::class)
    ->ignoring(PublicPageBenchmark::class);
