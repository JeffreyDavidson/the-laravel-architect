<?php

namespace App\ViewModels;

use App\Models\Post;
use App\Queries\RelatedPostsQuery;
use Illuminate\Database\Eloquent\Collection;

class PostShowViewModel
{
    public function __construct(
        private readonly RelatedPostsQuery $relatedPostsQuery,
    ) {}

    /**
     * @return array{
     *     post: Post,
     *     relatedPosts: Collection<int, Post>,
     *     seoSource: Post,
     * }
     */
    public function data(Post $post): array
    {
        $post->load(['category', 'tags', 'author']);

        return [
            'post' => $post,
            'relatedPosts' => $this->relatedPostsQuery->get($post),
            'seoSource' => $post,
        ];
    }
}
