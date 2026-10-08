<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Episode;
use App\Services\StoredMediaLifecycle;

/**
 * Episode artwork keeps only its original upload: episode pages do not render a srcset,
 * so no responsive variants are generated (no variant label is passed).
 */
final readonly class EpisodeObserver
{
    public function __construct(private StoredMediaLifecycle $media) {}

    public function updated(Episode $episode): void
    {
        $this->media->updated($episode, 'featured_image_path');
    }

    public function forceDeleted(Episode $episode): void
    {
        $this->media->forceDeleted($episode, 'featured_image_path');
    }
}
