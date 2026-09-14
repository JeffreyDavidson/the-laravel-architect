<?php

namespace App\ViewModels;

use App\Models\Category;
use App\Models\Post;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use RalphJSmit\Laravel\SEO\Support\SEOData;

class BlogIndexViewModel
{
    private const int POSTS_PER_PAGE = 12;

    /**
     * @param  array<string, mixed>  $filters
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
    public function data(array $filters = []): array
    {
        $queryInput = $filters['q'] ?? null;
        $categoryInput = $filters['category'] ?? null;
        $query = is_string($queryInput) ? trim($queryInput) : '';
        $categorySlug = is_string($categoryInput) ? $categoryInput : null;
        $selectedCategory = $categorySlug !== null && $categorySlug !== ''
            ? Category::query()->where('slug', $categorySlug)->firstOrFail()
            : null;

        $postsQuery = Post::query()
            ->select([
                'id',
                'title',
                'slug',
                'excerpt',
                'content',
                'featured_image_path',
                'category_id',
                'published_at',
            ])
            ->published()
            ->with([
                'category:id,name,slug',
                'tags:id,name,slug,type',
            ])
            ->orderByDesc('published_at')
            ->orderByDesc('id');

        if ($selectedCategory) {
            $postsQuery->whereBelongsTo($selectedCategory);
        }

        if ($query !== '') {
            $postsQuery->where(function (Builder $postsQuery) use ($query): void {
                $like = '%'.addcslashes($query, '\\%_').'%';

                $postsQuery
                    ->whereRaw("title LIKE ? ESCAPE '\\'", [$like])
                    ->orWhereRaw("excerpt LIKE ? ESCAPE '\\'", [$like])
                    ->orWhereHas('tags', function (Builder $tagQuery) use ($like): void {
                        $locale = app()->getLocale();
                        $tagQuery->whereRaw(
                            "json_extract(\"tags\".\"name\", ?) LIKE ? ESCAPE '\\'",
                            ["$.{$locale}", $like],
                        );
                    });
            });
        }

        $posts = $postsQuery
            ->paginate(self::POSTS_PER_PAGE)
            ->appends(array_filter([
                'q' => $query !== '' ? $query : null,
                'category' => $categorySlug,
            ], fn (?string $value): bool => $value !== null));

        abort_if($posts->currentPage() > $posts->lastPage(), 404);

        $publishedPostCount = $selectedCategory === null && $query === ''
            ? $posts->total()
            : Post::published()->count();

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
            'categories' => Category::query()
                ->withCount(['publishedPosts as posts_count'])
                ->get(),
            'publishedPostCount' => $publishedPostCount,
            'query' => $query,
            'categorySlug' => $categorySlug,
            'selectedCategory' => $selectedCategory,
            'seoSource' => new SEOData(
                title: $title,
                description: $description,
                url: $query === '' ? $canonicalUrl : $searchCanonicalUrl,
                canonical_url: $query === '' ? $canonicalUrl : $searchCanonicalUrl,
                robots: $query === '' ? null : 'noindex, follow',
            ),
        ];
    }
}
