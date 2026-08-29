<?php

namespace App\ViewModels;

use App\Models\Category;
use App\Models\Post;
use Illuminate\Database\Eloquent\Collection;
use RalphJSmit\Laravel\SEO\Support\SEOData;

class BlogIndexViewModel
{
    /**
     * @return array{
     *     posts: Collection<int, Post>,
     *     categories: Collection<int, Category>,
     *     seoSource: SEOData,
     * }
     */
    public function data(): array
    {
        return [
            'posts' => Post::published()
                ->with(['category', 'tags', 'author'])
                ->latest('published_at')
                ->get(),
            'categories' => Category::query()
                ->withCount(['publishedPosts as posts_count'])
                ->get(),
            'seoSource' => new SEOData(
                title: 'Blog',
                description: 'Articles on Laravel, PHP, architecture patterns, testing, and the craft of building modern web applications.',
            ),
        ];
    }
}
