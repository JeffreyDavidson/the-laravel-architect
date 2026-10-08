<?php

declare(strict_types=1);

namespace App\ViewModels;

use App\Contracts\PageViewModel;
use App\Data\PageMeta;
use App\Models\Category;
use App\Models\Post;
use App\Queries\BlogIndexQuery;
use App\Support\Seo\CollectionListing;
use App\Support\Seo\JsonLd;
use App\Support\Seo\PaginatedPageSeo;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use RalphJSmit\Laravel\SEO\Support\SEOData;

final readonly class PostIndexViewModel implements PageViewModel
{
    public function __construct(
        private BlogIndexQuery $blogIndexQuery,
        private SiteStructuredData $site,
    ) {}

    /**
     * The blog index for the validated search and category, shared by the page and the BlogIndex
     * component. An unknown category or a page past the last one is a 404. Page links always point
     * at the blog index, also when Livewire renders them, and keep the search and category.
     *
     * @return array{
     *     posts: LengthAwarePaginator<int, Post>,
     *     categories: Collection<int, Category>,
     *     publishedPostCount: int,
     *     query: string,
     *     categorySlug: string|null,
     *     selectedCategory: Category|null,
     *     pageMeta: PageMeta,
     * }
     */
    public function data(string $query, ?string $categorySlug): array
    {
        $selectedCategory = $categorySlug !== null && $categorySlug !== ''
            ? $this->blogIndexQuery->category($categorySlug) ?? abort(404)
            : null;

        $posts = $this->blogIndexQuery->posts($query, $selectedCategory)
            ->withPath(route('blog.index'))
            ->appends(array_filter([
                'q' => $query !== '' ? $query : null,
                'category' => $categorySlug,
            ], fn (?string $value): bool => $value !== null));

        $page = PaginatedPageSeo::forCurrentPage($posts);
        abort_if($page->isOutOfRange(), 404);

        $categories = $this->blogIndexQuery->categories();
        $publishedPostCount = ! $selectedCategory instanceof Category && $query === ''
            ? $posts->total()
            : $this->blogIndexQuery->publishedPostCount();

        $categoryUrl = $selectedCategory ? route('blog.category', $selectedCategory) : null;
        $canonicalUrl = $categoryUrl ?? $page->url('blog.index');
        $searchCanonicalUrl = $categoryUrl ?? route('blog.index');
        $title = $selectedCategory ? "{$selectedCategory->name} Articles" : 'Blog';
        $description = $selectedCategory
            ? "Articles about {$selectedCategory->name} — Laravel development insights from Jeffrey Davidson."
            : 'Articles on Laravel, PHP, architecture patterns, testing, and the craft of building modern web applications.';

        // The collection keeps the listing's name while a search retitles the page.
        $collectionName = $title;

        if ($query !== '') {
            $title = 'Search results';
            $description = "Search results for {$query} on The Laravel Architect.";
        }

        $pageUrl = $query === '' ? $canonicalUrl : $searchCanonicalUrl;

        return [
            'posts' => $posts,
            'categories' => $categories,
            'publishedPostCount' => $publishedPostCount,
            'query' => $query,
            'categorySlug' => $categorySlug,
            'selectedCategory' => $selectedCategory,
            'pageMeta' => new PageMeta(
                seo: new SEOData(
                    title: $page->title($title),
                    description: $page->description($description),
                    url: $pageUrl,
                    robots: $query === '' ? null : 'noindex, follow',
                    canonical_url: $pageUrl,
                ),
                structuredData: [
                    ...JsonLd::collectionPage(CollectionListing::paginated(
                        $collectionName,
                        $pageUrl,
                        $posts,
                        static fn (Post $post): array => ['name' => $post->title, 'url' => route('blog.show', $post)],
                    )),
                    $this->site->breadcrumbs([['name' => 'Blog', 'url' => $pageUrl]]),
                ],
            ),
        ];
    }
}
