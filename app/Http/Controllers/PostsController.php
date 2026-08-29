<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Post;
use App\Queries\RelatedPostsQuery;
use Illuminate\Contracts\View\View;
use RalphJSmit\Laravel\SEO\Support\SEOData;

class PostsController extends Controller
{
    public function index(): View
    {
        $posts = Post::published()
            ->with(['category', 'tags', 'author'])
            ->latest('published_at')
            ->get();

        $categories = Category::query()
            ->withCount(['publishedPosts as posts_count'])
            ->get();

        $seoSource = new SEOData(
            title: 'Blog',
            description: 'Articles on Laravel, PHP, architecture patterns, testing, and the craft of building modern web applications.',
        );

        return view('blog.index', [
            'posts' => $posts,
            'categories' => $categories,
            'seoSource' => $seoSource,
        ]);
    }

    public function show(Post $post, RelatedPostsQuery $relatedPostsQuery): View
    {
        abort_unless($post->isPublished(), 404);

        $post->load(['category', 'tags', 'author']);
        $relatedPosts = $relatedPostsQuery->get($post);

        $seoSource = $post;

        return view('blog.show', [
            'post' => $post,
            'relatedPosts' => $relatedPosts,
            'seoSource' => $seoSource,
        ]);
    }
}
