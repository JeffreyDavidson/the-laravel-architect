<?php

declare(strict_types=1);

namespace App\Support\Feeds;

use Carbon\CarbonInterface;

/**
 * Renders a sitemaps.org XML urlset on a single line. A URL without a modification date
 * omits its lastmod element. Locations are written as given, so pass URLs that need no
 * XML escaping, such as the slug-based URLs the router generates.
 */
final class SitemapRenderer
{
    /** @param  list<array{loc: string, lastmod: CarbonInterface|null, changefreq: string, priority: string}>  $urls */
    public function render(array $urls): string
    {
        $body = implode('', array_map($this->urlElement(...), $urls));

        return "<?xml version=\"1.0\" encoding=\"UTF-8\"?><urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">{$body}</urlset>";
    }

    /** @param  array{loc: string, lastmod: CarbonInterface|null, changefreq: string, priority: string}  $url */
    private function urlElement(array $url): string
    {
        $lastmodElement = $url['lastmod'] instanceof CarbonInterface
            ? "<lastmod>{$url['lastmod']->toW3cString()}</lastmod>"
            : '';

        return "<url><loc>{$url['loc']}</loc>{$lastmodElement}<changefreq>{$url['changefreq']}</changefreq><priority>{$url['priority']}</priority></url>";
    }
}
