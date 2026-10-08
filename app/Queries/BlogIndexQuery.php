<?php

declare(strict_types=1);

namespace App\Queries;

use App\Models\Category;
use App\Models\Post;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

final class BlogIndexQuery
{
    private const int POSTS_PER_PAGE = 12;

    public function category(string $slug): ?Category
    {
        return Category::query()
            ->where('slug', $slug)
            ->first();
    }

    /**
     * Published posts, newest first, in the category when one is given and matching the search
     * in the title, excerpt or a tag name when it is not empty.
     *
     * @return LengthAwarePaginator<int, Post>
     */
    public function posts(string $search, ?Category $category): LengthAwarePaginator
    {
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

        if ($category instanceof Category) {
            $postsQuery->whereBelongsTo($category);
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

        return $postsQuery->paginate(self::POSTS_PER_PAGE);
    }

    /** @return Collection<int, Category> */
    public function categories(): Collection
    {
        return Category::query()
            ->withCount(['publishedPosts as posts_count'])
            ->get();
    }

    public function publishedPostCount(): int
    {
        return Post::query()
            ->published()
            ->count();
    }
}
