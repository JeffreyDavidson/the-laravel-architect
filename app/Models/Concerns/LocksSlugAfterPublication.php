<?php

namespace App\Models\Concerns;

/**
 * Locks a record's slug once its content has been published, so a public URL is never
 * changed by hand (there are no redirects). The lock persists when the content is later
 * unpublished. Requires HasPublishingStatus and a nullable `slug_locked_at` column.
 */
trait LocksSlugAfterPublication
{
    protected static function bootLocksSlugAfterPublication(): void
    {
        static::saving(function (self $content): void {
            if ($content->getAttribute('slug_locked_at') !== null) {
                return;
            }

            $wasLive = $content->hasLivePublishingStatus($content->getOriginal(self::publishingStatusColumn()));

            if ($wasLive || $content->hasLivePublishingStatus($content->getAttribute(self::publishingStatusColumn()))) {
                $content->setAttribute('slug_locked_at', now());
            }
        });
    }

    /** Whether the slug can no longer be edited: it was locked, or the content is published or scheduled. */
    public function isSlugLocked(): bool
    {
        return $this->getAttribute('slug_locked_at') !== null
            || $this->hasLivePublishingStatus($this->getAttribute(self::publishingStatusColumn()));
    }

    /** A status of Published or Scheduled, whatever the publish date; an unset status is not live. */
    private function hasLivePublishingStatus(mixed $status): bool
    {
        return in_array($status, static::publishingStatuses(), true);
    }

    private static function publishingStatusColumn(): string
    {
        return static::publishingStatusConfiguration()->status ?? 'status';
    }
}
