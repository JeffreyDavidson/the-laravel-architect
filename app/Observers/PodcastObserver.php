<?php

namespace App\Observers;

use App\Models\Podcast;
use App\Services\ResponsiveImageVariants;
use Illuminate\Support\Facades\Log;

class PodcastObserver
{
    public function __construct(private readonly ResponsiveImageVariants $images) {}

    public function created(Podcast $podcast): void
    {
        $this->generateImages($podcast->cover_image_path);
    }

    public function updated(Podcast $podcast): void
    {
        if (! $podcast->wasChanged('cover_image_path')) {
            return;
        }

        $previousPath = $podcast->getPrevious()['cover_image_path'] ?? null;

        if (is_string($previousPath) && filled($previousPath)) {
            $this->images->delete($previousPath);
        }

        $this->generateImages($podcast->cover_image_path);
    }

    public function deleted(Podcast $podcast): void
    {
        if (is_string($podcast->cover_image_path) && filled($podcast->cover_image_path)) {
            $this->images->delete($podcast->cover_image_path);
        }
    }

    private function generateImages(mixed $path): void
    {
        if (! is_string($path) || blank($path)) {
            return;
        }

        if (! $this->images->generate($path)) {
            Log::warning('Responsive podcast image generation failed. Run podcasts:generate-image-variants to retry.');
        }
    }
}
