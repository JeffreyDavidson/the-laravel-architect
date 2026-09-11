<?php

it('defines a stable installable web application identity', function () {
    $manifest = readWebManifest();

    expect($manifest)
        ->toMatchArray([
            'id' => '/',
            'name' => 'The Laravel Architect',
            'short_name' => 'Laravel Architect',
            'start_url' => '/',
            'scope' => '/',
            'lang' => 'en-US',
            'display' => 'standalone',
        ])
        ->and($manifest['description'])->not->toBeEmpty();
});

it('provides every declared web application icon at its advertised dimensions', function () {
    $manifest = readWebManifest();

    foreach ($manifest['icons'] as $icon) {
        $path = public_path(ltrim($icon['src'], '/'));
        $dimensions = getimagesize($path);
        if ($dimensions === false) {
            throw new RuntimeException("Unable to read web manifest icon {$path}.");
        }

        expect($path)
            ->toBeFile()
            ->and("{$dimensions[0]}x{$dimensions[1]}")
            ->toBe($icon['sizes']);
    }
});

/**
 * @return array{id: string, name: string, short_name: string, start_url: string, scope: string, lang: string, display: string, description: string, icons: list<array{src: string, sizes: string}>}
 */
function readWebManifest(): array
{
    $contents = file_get_contents(public_path('site.webmanifest'));
    if ($contents === false) {
        throw new RuntimeException('Unable to read the web manifest.');
    }

    $manifest = json_decode($contents, true, flags: JSON_THROW_ON_ERROR);
    $requiredKeys = ['id', 'name', 'short_name', 'start_url', 'scope', 'lang', 'display', 'description'];
    if (! is_array($manifest) || ! is_array($manifest['icons'] ?? null)) {
        throw new RuntimeException('Web manifest has an invalid structure.');
    }

    foreach ($requiredKeys as $key) {
        if (! is_string($manifest[$key] ?? null)) {
            throw new RuntimeException("Web manifest field {$key} is invalid.");
        }
    }

    $icons = [];
    foreach ($manifest['icons'] as $icon) {
        if (! is_array($icon) || ! is_string($icon['src'] ?? null) || ! is_string($icon['sizes'] ?? null)) {
            throw new RuntimeException('Web manifest icon metadata is invalid.');
        }

        $icons[] = ['src' => $icon['src'], 'sizes' => $icon['sizes']];
    }

    return [
        'id' => $manifest['id'],
        'name' => $manifest['name'],
        'short_name' => $manifest['short_name'],
        'start_url' => $manifest['start_url'],
        'scope' => $manifest['scope'],
        'lang' => $manifest['lang'],
        'display' => $manifest['display'],
        'description' => $manifest['description'],
        'icons' => $icons,
    ];
}
