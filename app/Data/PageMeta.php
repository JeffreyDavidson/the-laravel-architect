<?php

declare(strict_types=1);

namespace App\Data;

use RalphJSmit\Laravel\SEO\Support\SEOData;

/**
 * What a public page tells search engines and social sites about itself: the SEO metadata the
 * site layout renders as tags, and the page's own JSON-LD nodes. The layout adds the site-wide
 * entities (SiteStructuredData) in front of these nodes, so they are only the page's.
 */
final readonly class PageMeta
{
    /**
     * @param  list<array<string, mixed>>  $structuredData
     */
    public function __construct(
        public SEOData $seo,
        public array $structuredData = [],
    ) {}
}
