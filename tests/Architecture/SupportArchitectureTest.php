<?php

arch('keeps models out of the feed renderers')
    ->expect('App\Support\Feeds')
    ->not->toUse('App\Models');
