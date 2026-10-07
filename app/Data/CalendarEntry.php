<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\CalendarEntryType;
use App\Enums\PublishStatus;

/**
 * A post or episode placed on the editorial calendar. A null date means the content is unscheduled.
 */
final readonly class CalendarEntry
{
    public function __construct(
        public ?string $date,
        public string $title,
        public CalendarEntryType $type,
        public PublishStatus $status,
        public string $url,
    ) {}
}
