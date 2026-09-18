<?php

namespace App\Queries;

use App\Models\Category;
use App\Models\Post;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

final class BlogIndexQuery
{
    private const int POSTS_PER_PAGE = 12;

    /**
     * @return array{
     *     posts: LengthAwarePaginator<int, Post>,
     *     categories: Collection<int, Category>,
     *     publishedPostCount: int,
     *     selectedCategory: Category|null,
     * }
     */
    public function results(string $search, ?string $categorySlug): array
    {
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

        if ($selectedCategory !== null) {
            $postsQuery->whereBelongsTo($selectedCategory);
        }

        if ($search !== '') {
            $postsQuery->where(function (Builder $postsQuery) use ($search): void {
                $like = '%'.addcslashes($search, '\\%_').'%';

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
                'q' => $search !== '' ? $search : null,
                'category' => $categorySlug,
            ], fn (?string $value): bool => $value !== null));

        abort_if($posts->currentPage() > $posts->lastPage(), 404);

        return [
            'posts' => $posts,
            'categories' => Category::query()
                ->withCount(['publishedPosts as posts_count'])
                ->get(),
            'publishedPostCount' => $selectedCategory === null && $search === ''
                ? $posts->total()
                : Post::published()->count(),
            'selectedCategory' => $selectedCategory,
        ];
    }
}
