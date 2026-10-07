<?php

use App\Support\Feeds\SitemapRenderer;
use Carbon\CarbonImmutable;

it('renders a urlset on one line and omits the modification date when there is none', function () {
    $xml = new SitemapRenderer()
        ->render([
            [
                'loc' => 'https://example.test',
                'lastmod' => CarbonImmutable::parse('2026-10-05 08:30:00', 'UTC'),
                'changefreq' => 'weekly',
                'priority' => '1.0',
            ],
            [
                'loc' => 'https://example.test/about',
                'lastmod' => null,
                'changefreq' => 'monthly',
                'priority' => '0.8',
            ],
        ]);

    expect($xml)->toBe(
        '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'
        .'<url><loc>https://example.test</loc><lastmod>2026-10-05T08:30:00+00:00</lastmod><changefreq>weekly</changefreq><priority>1.0</priority></url>'
        .'<url><loc>https://example.test/about</loc><changefreq>monthly</changefreq><priority>0.8</priority></url>'
        .'</urlset>',
    );
});

it('renders an empty urlset when there are no urls', function () {
    expect(new SitemapRenderer()->render([]))
        ->toBe('<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></urlset>');
});
