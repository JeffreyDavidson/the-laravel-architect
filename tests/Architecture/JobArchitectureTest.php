<?php

arch('dispatches queued jobs only from actions, other jobs, providers and console code')
    ->expect('App\Jobs')
    ->toOnlyBeUsedIn([
        'App\Actions',
        'App\Console',
        'App\Jobs',
        'App\Providers',
    ]);
