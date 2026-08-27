<?php

namespace App\Observers;

use App\Models\Post;
use App\Services\ResponsiveImageVariants;

class PostObserver
{
    public function __construct(private readonly ResponsiveImageVariants $images) {}

    public function created(Post $post): void
    {
        $this->generateImages($post->featured_image_path);
    }

    public function updated(Post $post): void
    {
        if (! $post->wasChanged('featured_image_path')) {
            return;
        }

        $previousPath = $post->getPrevious()['featured_image_path'] ?? null;

        if (is_string($previousPath) && filled($previousPath)) {
            $this->images->delete($previousPath);
        }

        $this->generateImages($post->featured_image_path);
    }

    public function deleted(Post $post): void
    {
        if (is_string($post->featured_image_path) && filled($post->featured_image_path)) {
            $this->images->delete($post->featured_image_path);
        }
    }

    private function generateImages(mixed $path): void
    {
        if (! is_string($path) || blank($path)) {
            return;
        }

        $this->images->generate($path);
    }
}
