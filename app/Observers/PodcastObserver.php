<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Episode;
use App\Models\Podcast;
use App\Services\ResponsiveImageLifecycle;

class PodcastObserver
{
    public function __construct(private readonly ResponsiveImageLifecycle $lifecycle) {}

    public function created(Podcast $podcast): void
    {
        $this->lifecycle->created($podcast, 'cover_image_path', 'podcast');
    }

    public function updated(Podcast $podcast): void
    {
        $this->lifecycle->updated($podcast, 'cover_image_path', 'podcast');
    }

    /**
     * Restore the episodes that were trashed together with the podcast, leaving episodes
     * that were trashed separately before it in the trash.
     */
    public function restoring(Podcast $podcast): void
    {
        $deletedAt = $podcast->getAttribute('deleted_at');

        if ($deletedAt === null) {
            return;
        }

        $podcast->episodes()
            ->onlyTrashed()
            ->where('deleted_at', '>=', $deletedAt)
            ->each(fn (Episode $episode): bool => $episode->restore());
    }

    public function forceDeleted(Podcast $podcast): void
    {
        $this->lifecycle->deleted($podcast, 'cover_image_path');
    }
}
