<?php

namespace App\Presenters;

use App\Data\ResponsiveImage;
use App\Models\Podcast;
use App\Services\ResponsiveImageVariants;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Vite;

/**
 * Presents a podcast's cover artwork and accent colour. The cover is the uploaded image, or the
 * bundled artwork in config('podcasts.fallback_artwork') for a known podcast without one.
 */
final readonly class PodcastPresenter
{
    private const string DEFAULT_COLOR = '#6366f1';

    public function __construct(
        private Podcast $podcast,
        private ResponsiveImageVariants $images,
    ) {}

    public static function from(Podcast $podcast): self
    {
        return new self($podcast, app(ResponsiveImageVariants::class));
    }

    public function coverImageUrl(): ?string
    {
        if ($this->podcast->cover_image_path) {
            return Storage::disk('public')
                ->url(
                    $this->podcast->cover_image_path,
                );
        }

        $resources = $this->fallbackArtworkResources();

        return $resources ? Vite::asset($resources[512]) : null;
    }

    /** The cover with the uploaded image's WebP variants, or the bundled artwork's sizes. */
    public function cover(): ?ResponsiveImage
    {
        $src = $this->coverImageUrl();

        if ($src === null) {
            return null;
        }

        return new ResponsiveImage(
            src: $src,
            srcset: $this->images->srcset($this->podcast->cover_image_path) ?? $this->fallbackArtworkSrcset(),
        );
    }

    /** The podcast's colour when it is a six-digit hex value, so it is safe inside a style attribute. */
    public function displayColor(): string
    {
        return is_string($this->podcast->color)
            && preg_match('/\A#[0-9a-fA-F]{6}\z/', $this->podcast->color) === 1
            ? $this->podcast->color
            : self::DEFAULT_COLOR;
    }

    /**
     * The podcast's listening platforms that have a link, in display order. The icon names the
     * platform's icon and colours in the platform-links component.
     *
     * @return list<array{label: string, url: string, icon: string}>
     */
    public function platformLinks(): array
    {
        $platforms = [
            ['label' => 'Spotify', 'url' => $this->podcast->spotify_url, 'icon' => 'spotify'],
            ['label' => 'Apple Podcasts', 'url' => $this->podcast->apple_url, 'icon' => 'apple-podcasts'],
            ['label' => 'YouTube', 'url' => $this->podcast->youtube_url, 'icon' => 'youtube'],
            ['label' => 'RSS', 'url' => $this->podcast->rss_url, 'icon' => 'rss'],
        ];

        $links = [];

        foreach ($platforms as $platform) {
            if (is_string($platform['url']) && $platform['url'] !== '') {
                $links[] = ['label' => $platform['label'], 'url' => $platform['url'], 'icon' => $platform['icon']];
            }
        }

        return $links;
    }

    private function fallbackArtworkSrcset(): ?string
    {
        if ($this->podcast->cover_image_path) {
            return null;
        }

        $resources = $this->fallbackArtworkResources();

        if (! $resources) {
            return null;
        }

        $srcset = [];

        foreach ($resources as $width => $resource) {
            $srcset[] = Vite::asset($resource)." {$width}w";
        }

        return implode(', ', $srcset);
    }

    /** @return array<int, string>|null */
    private function fallbackArtworkResources(): ?array
    {
        $artwork = config()->array('podcasts.fallback_artwork')[$this->podcast->slug] ?? null;

        if (! is_array($artwork)) {
            return null;
        }

        $resources = [];

        foreach ($artwork as $width => $resource) {
            if (is_int($width) && is_string($resource)) {
                $resources[$width] = $resource;
            }
        }

        return $resources ?: null;
    }
}
