<?php

declare(strict_types=1);

namespace App\Support;

use Carbon\CarbonInterface;
use IntlTimeZone;

/**
 * Timestamps are stored and compared in UTC. People enter and read them in the site's
 * display timezone (`app.display_timezone`), so a post published at 21:00 Eastern shows
 * that day's date rather than the next UTC day.
 */
final class DisplayTimezone
{
    public static function name(): string
    {
        return config()->string('app.display_timezone');
    }

    /** A readable name for helper text, such as "Eastern Time (America/New_York)". */
    public static function label(): string
    {
        $name = self::name();
        $readableName = IntlTimeZone::createTimeZone($name)->getDisplayName(false, IntlTimeZone::DISPLAY_LONG_GENERIC, 'en');

        if (! is_string($readableName)) {
            return $name;
        }

        return "{$readableName} ({$name})";
    }

    /**
     * The same instant expressed in the display timezone, leaving the given date untouched.
     *
     * @template TDate of CarbonInterface
     *
     * @param  TDate|null  $date
     * @return ($date is null ? null : TDate)
     */
    public static function convert(?CarbonInterface $date): ?CarbonInterface
    {
        return $date?->copy()
            ->setTimezone(self::name());
    }
}
