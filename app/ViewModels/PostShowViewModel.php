<?php

namespace App\ViewModels;

use App\Models\Post;
use App\Queries\RelatedPostsQuery;
use Illuminate\Database\Eloquent\Collection;
use RalphJSmit\Laravel\SEO\Support\SEOData;

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

    /**
     * @return array{
     *     post: Post,
     *     relatedPosts: Collection<int, Post>,
     *     seoSource: SEOData,
     * }
     */
    public function previewData(Post $post): array
    {
        $data = $this->data($post);
        $data['seoSource'] = new SEOData(
            title: $post->title.' — Preview',
            description: $post->excerpt,
            robots: 'noindex, nofollow',
        );

        return $data;
    }
}
