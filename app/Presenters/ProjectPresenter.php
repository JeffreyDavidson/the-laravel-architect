<?php

declare(strict_types=1);

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
        return app()->make(self::class, ['project' => $project]);
    }

    /** The uploaded featured image's URL, or null when the project has none. */
    public function featuredImageUrl(): ?string
    {
        $path = $this->project->featured_image_path;

        return is_string($path) && $path !== '' ? $this->images->url($path) : null;
    }

    /** The uploaded featured image with its WebP variants, or null when the project has none. */
    public function featuredImage(): ?ResponsiveImage
    {
        $src = $this->featuredImageUrl();

        if ($src === null) {
            return null;
        }

        return new ResponsiveImage($src, $this->images->srcset($this->project->featured_image_path));
    }
}
