<?php

namespace App\Presenters;

use App\Data\ResponsiveImage;
use App\Models\Project;
use App\Services\ResponsiveImageVariants;

final readonly class ProjectPresenter
{
    public function __construct(
        private Project $project,
        private ResponsiveImageVariants $images,
    ) {}

    public static function from(Project $project): self
    {
        return new self($project, app(ResponsiveImageVariants::class));
    }

    /** The uploaded featured image with its WebP variants, or null when the project has none. */
    public function featuredImage(): ?ResponsiveImage
    {
        $src = $this->project->featured_image_url;

        if ($src === null) {
            return null;
        }

        return new ResponsiveImage($src, $this->images->srcset($this->project->featured_image_path));
    }
}
