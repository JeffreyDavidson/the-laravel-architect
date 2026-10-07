<?php

declare(strict_types=1);

namespace App\Presenters;

use App\Data\ResponsiveImage;
use App\Enums\BundledPostArtwork;
use App\Models\Post;
use App\Presenters\Concerns\LinksToPublicPageOrPreview;
use App\Services\ResponsiveImageVariants;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Foundation\Vite;

final readonly class PostPresenter
{
    use LinksToPublicPageOrPreview;

    public function __construct(
        private Post $post,
        private ResponsiveImageVariants $images,
        private UrlGenerator $urls,
        private Vite $vite,
    ) {}

    public static function from(Post $post): self
    {
        return app()->make(self::class, ['post' => $post]);
    }

    public function publicUrl(): ?string
    {
        if (! $this->post->isPublished()) {
            return null;
        }

        return $this->urls->route('blog.show', $this->post);
    }

    public function previewUrl(): string
    {
        return $this->signedPreviewUrl($this->urls, 'preview.post', ['post' => $this->post]);
    }

    public function readingTime(): int
    {
        return max(1, (int) ceil(str_word_count(strip_tags($this->post->content)) / 250));
    }

    /** The uploaded featured image's URL, or null when the post has none. */
    public function featuredImageUrl(): ?string
    {
        $path = $this->post->featured_image_path;

        return is_string($path) && $path !== '' ? $this->images->url($path) : null;
    }

    /**
     * The post's artwork: the uploaded featured image with its WebP variants, then the bundled
     * launch artwork. Null when the post has neither.
     */
    public function artwork(): ?ResponsiveImage
    {
        $uploadedUrl = $this->featuredImageUrl();
        $bundledUrls = $this->bundledArtworkUrls();
        $src = $uploadedUrl ?? $bundledUrls['large'] ?? null;

        if ($src === null) {
            return null;
        }

        $uploadedSrcset = $uploadedUrl !== null
            ? $this->images->srcset($this->post->featured_image_path)
            : null;
        $bundledSrcset = $bundledUrls !== null
            ? "{$bundledUrls['small']} 384w, {$bundledUrls['medium']} 768w, {$bundledUrls['large']} 1280w"
            : null;

        return new ResponsiveImage($src, $uploadedSrcset ?? $bundledSrcset);
    }

    /**
     * The wide image a published post shares in social cards and its Article structured data:
     * the uploaded featured image, then the bundled launch artwork, then the generated OG card.
     */
    public function shareImageUrl(): string
    {
        return $this->featuredImageUrl()
            ?? $this->bundledArtworkUrls()['large']
            ?? $this->urls->route('og-image', $this->post);
    }

    /**
     * The bundled launch artwork's sizes for a post whose slug has some, or null.
     *
     * @return array{small: string, medium: string, large: string}|null
     */
    private function bundledArtworkUrls(): ?array
    {
        $image = BundledPostArtwork::tryFrom($this->post->slug)?->imageName();

        if ($image === null) {
            return null;
        }

        return [
            'small' => $this->vite->asset("resources/images/{$image}-384.webp"),
            'medium' => $this->vite->asset("resources/images/{$image}-768.webp"),
            'large' => $this->vite->asset("resources/images/{$image}-1280.webp"),
        ];
    }
}
