<?php

use App\Support\Feeds\RobotsTxtRenderer;

it('lists the disallowed paths and the sitemap when crawling is allowed', function () {
    $robots = new RobotsTxtRenderer()
        ->render(true, ['/admin', '/preview/'], 'https://example.test/sitemap.xml');

    expect($robots)->toBe(implode("\n", [
        'User-agent: *',
        'Allow: /',
        '',
        'Disallow: /admin',
        'Disallow: /preview/',
        '',
        'Sitemap: https://example.test/sitemap.xml',
        '',
    ]));
});

it('disallows every path when crawling is not allowed', function () {
    $robots = new RobotsTxtRenderer()
        ->render(false, ['/admin'], 'https://example.test/sitemap.xml');

    expect($robots)->toBe("User-agent: *\nDisallow: /\n");
});
