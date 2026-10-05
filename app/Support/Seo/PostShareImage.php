<?php

declare(strict_types=1);

namespace App\Support\Seo;

use App\Models\Post;
use App\Support\Content\BundledPostArtwork;

/**
 * The wide image a published post shares in social cards and its Article structured data:
 * the uploaded featured image, then the bundled launch artwork, then the generated OG card.
 */
final readonly class PostShareImage
{
    public function __construct(private BundledPostArtwork $bundledPostArtwork) {}

    public function url(Post $post): string
    {
        return $post->featured_image_url
            ?? $this->bundledPostArtwork->urls($post->slug)['large']
            ?? route('og-image', $post);
    }
}
