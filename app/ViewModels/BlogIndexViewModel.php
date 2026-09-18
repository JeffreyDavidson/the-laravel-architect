<?php

declare(strict_types=1);

namespace App\ViewModels;

use App\Models\Category;
use App\Models\Post;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use RalphJSmit\Laravel\SEO\Support\SEOData;

class BlogIndexViewModel
{
    /**
     * @param  array{
     *     posts: LengthAwarePaginator<int, Post>,
     *     categories: Collection<int, Category>,
     *     publishedPostCount: int,
     *     selectedCategory: Category|null,
     * }  $results
     * @return array{
     *     posts: LengthAwarePaginator<int, Post>,
     *     categories: Collection<int, Category>,
     *     publishedPostCount: int,
     *     query: string,
     *     categorySlug: string|null,
     *     selectedCategory: Category|null,
     *     seoSource: SEOData,
     * }
     */
    public function data(array $results, string $query, ?string $categorySlug): array
    {
        $posts = $results['posts'];
        $categories = $results['categories'];
        $publishedPostCount = $results['publishedPostCount'];
        $selectedCategory = $results['selectedCategory'];

        $canonicalParameters = array_filter([
            'category' => $categorySlug,
            'page' => $posts->onFirstPage() ? null : $posts->currentPage(),
        ], fn (string|int|null $value): bool => $value !== null);
        $canonicalUrl = route('blog.index', $canonicalParameters);
        $searchCanonicalUrl = route('blog.index', array_filter(
            ['category' => $categorySlug],
            fn (?string $value): bool => $value !== null,
        ));
        $title = $selectedCategory ? "{$selectedCategory->name} Articles" : 'Blog';
        $description = $selectedCategory
            ? "Articles about {$selectedCategory->name} — Laravel development insights from Jeffrey Davidson."
            : 'Articles on Laravel, PHP, architecture patterns, testing, and the craft of building modern web applications.';

        if ($query !== '') {
            $title = 'Search results';
            $description = "Search results for {$query} on The Laravel Architect.";
        }

        if (! $posts->onFirstPage()) {
            $title .= " — Page {$posts->currentPage()}";
            $description .= " Page {$posts->currentPage()} of {$posts->lastPage()}.";
        }

        return [
            'posts' => $posts,
            'categories' => $categories,
            'publishedPostCount' => $publishedPostCount,
            'query' => $query,
            'categorySlug' => $categorySlug,
            'selectedCategory' => $selectedCategory,
            'seoSource' => new SEOData(
                title: $title,
                description: $description,
                url: $query === '' ? $canonicalUrl : $searchCanonicalUrl,
                robots: $query === '' ? null : 'noindex, follow',
                canonical_url: $query === '' ? $canonicalUrl : $searchCanonicalUrl,
            ),
        ];
    }
}
