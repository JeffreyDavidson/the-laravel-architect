<?php

declare(strict_types=1);

namespace App\Queries;

use App\Models\Post;
use Illuminate\Database\Eloquent\Collection;

final class RssFeedQuery
{
    /**
     * The twenty newest published posts with their category, newest first.
     *
     * @return Collection<int, Post>
     */
    public function get(): Collection
    {
        return Post::query()->published()
            ->latest('published_at')
            ->latest('id')
            ->with('category')
            ->take(20)
            ->get();
    }
}
