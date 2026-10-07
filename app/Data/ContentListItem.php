<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\SearchContentType;
use Carbon\CarbonImmutable;

/**
 * One piece of public content in a mixed listing such as the archive. It carries the
 * identifiers needed to link to the content and its raw date (in UTC); display formatting
 * belongs to the page that lists it.
 */
final readonly class ContentListItem
{
    public function __construct(
        public SearchContentType $type,
        public string $title,
        public ?string $summary,
        public string $slug,
        public CarbonImmutable $date,
        public ?string $podcastSlug = null,
        public ?string $youtubeId = null,
    ) {}
}
