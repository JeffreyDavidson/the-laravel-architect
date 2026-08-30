<?php

it('defines a stable installable web application identity', function () {
    $manifest = json_decode(
        file_get_contents(public_path('site.webmanifest')),
        true,
        flags: JSON_THROW_ON_ERROR,
    );

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
    $manifest = json_decode(
        file_get_contents(public_path('site.webmanifest')),
        true,
        flags: JSON_THROW_ON_ERROR,
    );

    foreach ($manifest['icons'] as $icon) {
        $path = public_path(ltrim($icon['src'], '/'));
        [$width, $height] = getimagesize($path);

        expect($path)
            ->toBeFile()
            ->and("{$width}x{$height}")
            ->toBe($icon['sizes']);
    }
});
