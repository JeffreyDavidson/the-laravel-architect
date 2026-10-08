<?php

declare(strict_types=1);

namespace App\ViewModels;

use App\Contracts\PageViewModel;
use App\Data\PageMeta;
use App\Models\Category;
use App\Models\Post;
use App\Support\Seo\CollectionListing;
use App\Support\Seo\JsonLd;
use App\Support\Seo\PaginatedPageSeo;
use Illuminate\Pagination\LengthAwarePaginator;
use RalphJSmit\Laravel\SEO\Support\SEOData;

final readonly class BlogCategoryViewModel implements PageViewModel
{
    public function __construct(private SiteStructuredData $site) {}

    /**
     * @return array{
     *     category: Category,
     *     posts: LengthAwarePaginator<int, Post>,
     *     pageMeta: PageMeta,
     * }
     */
    public function data(Category $category): array
    {
        $posts = $category->posts()
            ->published()
            ->with(['tags', 'author'])
            ->latest('published_at')
            ->latest('id')
            ->paginate(10);

        $page = PaginatedPageSeo::forCurrentPage($posts);
        abort_if($page->isOutOfRange(), 404);
        $canonicalUrl = $page->url('blog.category', ['category' => $category]);

        return [
            'category' => $category,
            'posts' => $posts,
            'pageMeta' => new PageMeta(
                seo: new SEOData(
                    title: $page->title("{$category->name} Articles"),
                    description: $page->description("Articles about {$category->name} — Laravel development insights from Jeffrey Davidson."),
                    url: $canonicalUrl,
                    canonical_url: $canonicalUrl,
                ),
                structuredData: [
                    ...JsonLd::collectionPage(CollectionListing::paginated(
                        "{$category->name} Articles",
                        $canonicalUrl,
                        $posts,
                        static fn (Post $post): array => ['name' => $post->title, 'url' => route('blog.show', $post)],
                    )),
                    $this->site->breadcrumbs([
                        ['name' => 'Blog', 'url' => route('blog.index')],
                        ['name' => $category->name, 'url' => $canonicalUrl],
                    ]),
                ],
            ),
        ];
    }
}
