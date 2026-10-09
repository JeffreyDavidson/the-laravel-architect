<?php

declare(strict_types=1);

namespace App\Presenters;

use App\Data\ResponsiveImage;
use App\Models\Project;
use App\Presenters\Concerns\LinksToPublicPageOrPreview;
use Illuminate\Contracts\Routing\UrlGenerator;
use JeffreyDavidson\CreatorKit\Services\Media\ResponsiveImageVariants;

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

    /**
     * The project as a schema.org CreativeWork case study. Repository links stay private, so only
     * the public website link appears, as sameAs.
     *
     * @param  array{'@type': string, '@id': string}  $author  A reference to the site's author.
     * @return array<string, mixed>
     */
    public function creativeWorkSchema(array $author): array
    {
        $projectUrl = $this->urls->route('projects.show', $this->project);
        $schema = [
            '@type' => 'CreativeWork',
            '@id' => "{$projectUrl}#project",
            'name' => $this->project->title,
            'url' => $projectUrl,
            'mainEntityOfPage' => $projectUrl,
            'description' => $this->project->description,
            'author' => $author,
        ];

        $featuredImageUrl = $this->featuredImageUrl();

        if ($featuredImageUrl) {
            $schema['image'] = $featuredImageUrl;
        }

        $technologyNames = is_array($this->project->tech_stack)
            ? array_values(array_filter($this->project->tech_stack, is_string(...)))
            : [];

        if ($technologyNames !== []) {
            $schema['keywords'] = implode(', ', $technologyNames);
        }

        if ($this->project->url) {
            $schema['sameAs'] = [$this->project->url];
        }

        return $schema;
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
