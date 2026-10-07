<?php

namespace App\ViewModels;

use App\Models\Post;
use App\Presenters\PostPresenter;
use App\Queries\RelatedPostsQuery;
use Illuminate\Database\Eloquent\Collection;
use RalphJSmit\Laravel\SEO\Models\SEO;
use RalphJSmit\Laravel\SEO\Support\SEOData;

class PostShowViewModel
{
    public function __construct(
        private readonly RelatedPostsQuery $relatedPostsQuery,
    ) {}

    /**
     * Shares the post as an article with a wide image, keeping any SEO fields saved in the admin.
     *
     * @return array{
     *     post: Post,
     *     relatedPosts: Collection<int, Post>,
     *     seoSource: SEOData,
     * }
     */
    public function data(Post $post): array
    {
        $post->load(['category', 'tags', 'author']);

        $seo = $post->seo;
        $seoSource = $seo instanceof SEO
            ? $seo->prepareForUsage()
            : $post->getDynamicSEOData();
        $seoSource->image = PostPresenter::from($post)->shareImageUrl();

        return [
            'post' => $post,
            'relatedPosts' => $this->relatedPostsQuery->get($post),
            'seoSource' => $seoSource,
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
