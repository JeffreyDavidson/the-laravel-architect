<?php

namespace App\ViewModels;

use App\Models\Category;
use App\Models\Post;
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
            ->paginate(10);

        abort_if($posts->currentPage() > $posts->lastPage(), 404);

        $canonicalUrl = $posts->onFirstPage()
            ? route('blog.category', $category)
            : route('blog.category', ['category' => $category, 'page' => $posts->currentPage()]);
        $title = "{$category->name} Articles";
        $description = "Articles about {$category->name} — Laravel development insights from Jeffrey Davidson.";

        if (! $posts->onFirstPage()) {
            $title .= " — Page {$posts->currentPage()}";
            $description .= " Page {$posts->currentPage()} of {$posts->lastPage()}.";
        }

        return [
            'category' => $category,
            'posts' => $posts,
            'seoSource' => new SEOData(
                title: $title,
                description: $description,
                url: $canonicalUrl,
                canonical_url: $canonicalUrl,
            ),
        ];
    }
}
