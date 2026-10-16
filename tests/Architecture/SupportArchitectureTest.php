<?php

use Illuminate\Http\Request;

// app/Support may use other Support classes only: every other App namespace, Filament and Livewire are off limits.
arch('keeps support classes free of the application layers, the request and HTTP aborts')
    ->expect('App\Support')
    ->not->toUse([
        'App\Actions',
        'App\Console',
        'App\Data',
        'App\Enums',
        'App\Filament',
        'App\Http',
        'App\Jobs',
        'App\Listeners',
        'App\Livewire',
        'App\Mail',
        'App\Models',
        'App\Notifications',
        'App\Observers',
        'App\Presenters',
        'App\Providers',
        'App\Publishing',
        'App\Queries',
        'App\Rules',
        'App\Services',
        'App\View',
        'App\ViewModels',
        'Filament',
        'Livewire',
        Request::class,
        'abort',
        'abort_if',
        'abort_unless',
    ]);
