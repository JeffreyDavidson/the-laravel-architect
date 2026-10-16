<?php

use Illuminate\Http\Request;

// Every app/ folder except Support, read from disk so a new layer is covered as soon as it exists.
$applicationLayers = array_values(array_map(
    static fn (string $folder): string => "App\\{$folder}",
    array_filter(
        scandir(dirname(__DIR__, 2).'/app') ?: [],
        static fn (string $entry): bool => $entry !== 'Support' && ! str_starts_with($entry, '.') && is_dir(dirname(__DIR__, 2)."/app/{$entry}"),
    ),
));

// app/Support may not use the rest of the application, Filament, Livewire, the Request or HTTP aborts.
arch('keeps support classes free of the application layers, the request and HTTP aborts')
    ->expect('App\\Support')
    ->not->toUse([
        ...$applicationLayers,
        'Filament',
        'Livewire',
        Request::class,
        'abort',
        'abort_if',
        'abort_unless',
    ]);
