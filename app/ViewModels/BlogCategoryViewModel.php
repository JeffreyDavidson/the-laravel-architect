<?php

namespace App\ViewModels;

use App\Models\Category;
use App\Models\Post;
use App\Support\Seo\PaginatedPageSeo;
use Illuminate\Pagination\LengthAwarePaginator;
use RalphJSmit\Laravel\SEO\Support\SEOData;

class BlogCategoryViewModel
{
    /**
     * @return array{
     *     category: Category,
     *     posts: LengthAwarePaginator<int, Post>,
     *     seoSource: SEOData,
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
        $canonicalUrl = $page->url('blog.category', ['category' => $category]);

        return [
            'category' => $category,
            'posts' => $posts,
            'seoSource' => new SEOData(
                title: $page->title("{$category->name} Articles"),
                description: $page->description("Articles about {$category->name} — Laravel development insights from Jeffrey Davidson."),
                url: $canonicalUrl,
                canonical_url: $canonicalUrl,
            ),
        ];
    }
}
