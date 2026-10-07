<?php

declare(strict_types=1);

namespace App\Queries;

use App\Models\Post;
use Illuminate\Database\Eloquent\Collection;

final class RelatedPostsQuery
{
    /** @return Collection<int, Post> */
    public function get(Post $post, int $limit = 3): Collection
    {
        if ($limit < 1) {
            return new Collection;
        }

        $post->loadMissing('tags');

        $relatedPosts = Post::query()->published()
            ->whereKeyNot($post->getKey())
            ->where('category_id', $post->category_id)
            ->with(['category', 'tags'])
            ->latest('published_at')
            ->latest('id')
            ->take($limit)
            ->get();

        if ($relatedPosts->count() < $limit && $post->tags->isNotEmpty()) {
            $tagRelatedPosts = Post::query()->published()
                ->whereNotIn('id', $relatedPosts->modelKeys())
                ->whereKeyNot($post->getKey())
                ->withAnyTags($post->tags)
                ->with(['category', 'tags'])
                ->latest('published_at')
                ->latest('id')
                ->take($limit - $relatedPosts->count())
                ->get();

            $relatedPosts = $relatedPosts->merge($tagRelatedPosts);
        }

        if ($relatedPosts->count() < $limit) {
            $latestPosts = Post::query()->published()
                ->whereNotIn('id', $relatedPosts->modelKeys())
                ->whereKeyNot($post->getKey())
                ->with(['category', 'tags'])
                ->latest('published_at')
                ->latest('id')
                ->take($limit - $relatedPosts->count())
                ->get();

            $relatedPosts = $relatedPosts->merge($latestPosts);
        }

        return $relatedPosts;
    }
}
