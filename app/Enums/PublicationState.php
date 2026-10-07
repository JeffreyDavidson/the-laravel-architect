<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * Where content is in its public lifecycle, as offered by the admin publication filter.
 * Unlike PublishStatus it accounts for the publish date: scheduled content is not live yet.
 * Live keeps the value "published" so existing links to the posts filter still work.
 */
enum PublicationState: string implements HasLabel
{
    case Live = 'published';
    case Scheduled = 'scheduled';
    case Unpublished = 'unpublished';

    public function getLabel(): string
    {
        return match ($this) {
            self::Live => 'Live on the site',
            self::Scheduled => 'Scheduled for later',
            self::Unpublished => 'Not yet published',
        };
    }

    /** The model query scope that selects content in this state. */
    public function scope(): string
    {
        return match ($this) {
            self::Live => 'published',
            self::Scheduled => 'scheduled',
            self::Unpublished => 'unpublished',
        };
    }
}
