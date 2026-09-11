<?php

namespace App\Observers;

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

    public function deleted(Podcast $podcast): void
    {
        $this->lifecycle->deleted($podcast, 'cover_image_path');
    }
}
