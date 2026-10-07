<?php

arch('keeps HTTP concerns and aborts out of queries')
    ->expect('App\Queries')
    ->not->toUse([
        'App\Http',
        'abort',
        'abort_if',
        'abort_unless',
    ]);
