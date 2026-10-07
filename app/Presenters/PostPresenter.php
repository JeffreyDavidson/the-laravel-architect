<?php

declare(strict_types=1);

namespace App\Presenters;

use App\Data\ResponsiveImage;
use App\Models\Post;
use App\Services\ResponsiveImageVariants;
use App\Support\Content\BundledPostArtwork;

final readonly class PostPresenter
{
    public function __construct(
        private Post $post,
        private BundledPostArtwork $bundledArtwork,
        private ResponsiveImageVariants $images,
    ) {}

    public static function from(Post $post): self
    {
        return new self($post, app(BundledPostArtwork::class), app(ResponsiveImageVariants::class));
    }

    public function readingTime(): int
    {
        return max(1, (int) ceil(str_word_count(strip_tags($this->post->content)) / 250));
    }

    /**
     * The post's artwork: the uploaded featured image with its WebP variants, then the bundled
     * launch artwork. Null when the post has neither.
     */
    public function artwork(): ?ResponsiveImage
    {
        $uploadedUrl = $this->post->featured_image_url;
        $bundledUrls = $this->bundledArtwork->urls($this->post->slug);
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
        return $this->post->featured_image_url
            ?? $this->bundledArtwork->urls($this->post->slug)['large']
            ?? route('og-image', $this->post);
    }
}
