<?php

declare(strict_types=1);

namespace App\Support\Feeds;

/**
 * Renders robots.txt for every user agent. A crawlable site lists its disallowed paths
 * and advertises the sitemap; otherwise every path is disallowed.
 */
final class RobotsTxtRenderer
{
    /** @param  list<string>  $disallowedPaths */
    public function render(bool $allowsCrawling, array $disallowedPaths, string $sitemapUrl): string
    {
        if (! $allowsCrawling) {
            return "User-agent: *\nDisallow: /\n";
        }

        return implode("\n", [
            'User-agent: *',
            'Allow: /',
            '',
            ...array_map(fn (string $path): string => "Disallow: {$path}", $disallowedPaths),
            '',
            "Sitemap: {$sitemapUrl}",
            '',
        ]);
    }
}
