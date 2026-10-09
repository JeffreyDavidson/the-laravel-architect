<?php

declare(strict_types=1);

namespace App\ViewModels;

use App\Models\Post;
use App\Presenters\PostPresenter;
use App\Queries\RelatedPostsQuery;
use Illuminate\Database\Eloquent\Collection;
use JeffreyDavidson\CreatorKit\Contracts\PageViewModel;
use JeffreyDavidson\CreatorKit\Data\PageMeta;
use JeffreyDavidson\CreatorKit\ViewModels\Concerns\AppliesStoredSeo;
use RalphJSmit\Laravel\SEO\Support\SEOData;

final readonly class PostShowViewModel implements PageViewModel
{
    use AppliesStoredSeo;

    public function __construct(
        private RelatedPostsQuery $relatedPostsQuery,
        private SiteStructuredData $site,
    ) {}

    /**
     * Shares the post as an article with a wide image, keeping any SEO fields saved in the admin.
     *
     * @return array{
     *     post: Post,
     *     relatedPosts: Collection<int, Post>,
     *     pageMeta: PageMeta,
     * }
     */
    public function data(Post $post): array
    {
        $data = $this->pageData($post);
        $presenter = PostPresenter::from($post);

        return [
            ...$data,
            'pageMeta' => new PageMeta(
                seo: $this->withStoredSeo($post, new SEOData(
                    title: $post->title,
                    description: $post->excerpt,
                    image: $presenter->shareImageUrl(),
                    published_time: $post->published_at,
                    modified_time: $post->updated_at,
                    type: 'article',
                )),
                structuredData: [
                    $presenter->articleSchema($this->site->authorReference()),
                    $this->site->breadcrumbs([
                        ['name' => 'Blog', 'url' => route('blog.index')],
                        ['name' => $post->title, 'url' => route('blog.show', $post)],
                    ]),
                ],
            ),
        ];
    }

    /**
     * @return array{
     *     post: Post,
     *     relatedPosts: Collection<int, Post>,
     *     pageMeta: PageMeta,
     * }
     */
    public function previewData(Post $post): array
    {
        return [
            ...$this->pageData($post),
            'pageMeta' => new PageMeta(new SEOData(
                title: $post->title.' — Preview',
                description: $post->excerpt,
                robots: 'noindex, nofollow',
            )),
        ];
    }

    /**
     * @return array{
     *     post: Post,
     *     relatedPosts: Collection<int, Post>,
     * }
     */
    private function pageData(Post $post): array
    {
        $post->load(['category', 'tags', 'author']);

        return [
            'post' => $post,
            'relatedPosts' => $this->relatedPostsQuery->get($post),
        ];
    }
}
