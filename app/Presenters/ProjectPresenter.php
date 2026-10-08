<?php

declare(strict_types=1);

namespace App\Presenters;

use App\Data\ResponsiveImage;
use App\Models\Project;
use App\Presenters\Concerns\LinksToPublicPageOrPreview;
use App\Services\ResponsiveImageVariants;
use Illuminate\Contracts\Routing\UrlGenerator;

final readonly class ProjectPresenter
{
    use LinksToPublicPageOrPreview;

    public function __construct(
        private Project $project,
        private ResponsiveImageVariants $images,
        private UrlGenerator $urls,
    ) {}

    public static function from(Project $project): self
    {
        return app()->make(self::class, ['project' => $project]);
    }

    public function publicUrl(): ?string
    {
        if (! $this->project->isPublished()) {
            return null;
        }

        return $this->urls->route('projects.show', $this->project);
    }

    public function previewUrl(): string
    {
        return $this->signedPreviewUrl($this->urls, 'preview.project', ['project' => $this->project]);
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
