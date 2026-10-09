<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Post;
use App\Services\OgImageCache;
use JeffreyDavidson\CreatorKit\Services\Media\StoredMediaLifecycle;

final readonly class PostObserver
{
    public function __construct(
        private OgImageCache $ogImageCache,
        private StoredMediaLifecycle $media,
    ) {}

    public function created(Post $post): void
    {
        $this->media->created($post, 'featured_image_path', 'post');
    }

    public function updated(Post $post): void
    {
        $this->media->updated($post, 'featured_image_path', 'post');
    }

    public function forceDeleted(Post $post): void
    {
        $this->media->forceDeleted($post, 'featured_image_path', 'post');

        $postKey = $post->getKey();

        if (! is_int($postKey) && ! is_string($postKey)) {
            return;
        }

        $post->getConnection()
            ->afterCommit(function () use ($postKey): void {
                $this->ogImageCache->forgetByKey($postKey);
            });
    }
}
