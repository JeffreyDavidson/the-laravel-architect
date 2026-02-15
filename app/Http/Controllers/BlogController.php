<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;

class BlogController extends Controller
{
    public function index()
    {
        $posts = Post::published()
            ->with(['category', 'tags', 'author'])
            ->latest('published_at')
            ->paginate(10);

        $categories = Category::withCount('posts')->get();

        seo()
            ->title('Blog')
            ->description('Articles on Laravel, PHP, architecture patterns, testing, and the craft of building modern web applications.');

        return view('blog.index', compact('posts', 'categories'));
    }

    public function show(Post $post)
    {
        abort_unless($post->status === 'published', 404);

        $post->load(['category', 'tags', 'author']);

        $relatedPosts = Post::published()
            ->where('id', '!=', $post->id)
            ->where('category_id', $post->category_id)
            ->latest('published_at')
            ->take(3)
            ->get();

        return view('blog.show', compact('post', 'relatedPosts'));
    }

    public function category(Category $category)
    {
        $posts = $category->posts()
            ->published()
            ->with(['tags', 'author'])
            ->latest('published_at')
            ->paginate(10);

        seo()
            ->title($category->name . ' Articles')
            ->description("Articles about {$category->name} — Laravel development insights from Jeffrey Davidson.");

        return view('blog.category', compact('category', 'posts'));
    }

    public function tag(Tag $tag)
    {
        $posts = Post::published()
            ->withAnyTags([$tag])
            ->with(['category', 'author'])
            ->latest('published_at')
            ->paginate(10);

        seo()
            ->title($tag->name . ' Articles')
            ->description("Articles tagged with {$tag->name} on The Laravel Architect.");

        return view('blog.tag', compact('tag', 'posts'));
    }
}
