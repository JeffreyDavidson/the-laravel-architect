<?php

namespace App\Observers;

use App\Models\Project;
use App\Services\ResponsiveImageVariants;
use Illuminate\Support\Facades\Log;

class ProjectObserver
{
    public function __construct(private readonly ResponsiveImageVariants $images) {}

    public function created(Project $project): void
    {
        $this->generateImages($project->featured_image_path);
    }

    public function updated(Project $project): void
    {
        if (! $project->wasChanged('featured_image_path')) {
            return;
        }

        $previousPath = $project->getPrevious()['featured_image_path'] ?? null;

        if (is_string($previousPath) && filled($previousPath)) {
            $this->images->delete($previousPath);
        }

        $this->generateImages($project->featured_image_path);
    }

    public function deleted(Project $project): void
    {
        if (is_string($project->featured_image_path) && filled($project->featured_image_path)) {
            $this->images->delete($project->featured_image_path);
        }
    }

    private function generateImages(mixed $path): void
    {
        if (! is_string($path) || blank($path)) {
            return;
        }

        if (! $this->images->generate($path)) {
            Log::warning('Responsive project image generation failed. Run projects:generate-image-variants to retry.');
        }
    }
}
