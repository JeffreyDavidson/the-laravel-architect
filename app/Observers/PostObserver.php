<?php

namespace App\Observers;

use App\Models\Post;
use App\Services\ResponsiveImageLifecycle;

class PostObserver
{
    public function __construct(private readonly ResponsiveImageLifecycle $lifecycle) {}

    public function created(Post $post): void
    {
        $this->lifecycle->created($post, 'featured_image_path', 'post');
    }

    public function updated(Post $post): void
    {
        $this->lifecycle->updated($post, 'featured_image_path', 'post');
    }

    public function deleted(Post $post): void
    {
        $this->lifecycle->deleted($post, 'featured_image_path');
    }
}
