<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use Illuminate\Contracts\View\View;
use RalphJSmit\Laravel\SEO\Support\SEOData;

class BlogController extends Controller
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

        return view('blog.index', compact('posts', 'categories', 'seoSource'));
    }

    public function show(Post $post): View
    {
        abort_unless($post->isPublished(), 404);

        $post->load(['category', 'tags', 'author']);

        // Same category first
        $relatedPosts = Post::published()
            ->where('id', '!=', $post->id)
            ->where('category_id', $post->category_id)
            ->with(['category', 'tags'])
            ->latest('published_at')
            ->take(3)
            ->get();

        // Fill with shared-tag posts if needed
        if ($relatedPosts->count() < 3 && $post->tags->count()) {
            $exclude = $relatedPosts->pluck('id')->push($post->id);

            $tagRelated = Post::published()
                ->whereNotIn('id', $exclude)
                ->withAnyTags($post->tags)
                ->with(['category', 'tags'])
                ->latest('published_at')
                ->take(3 - $relatedPosts->count())
                ->get();

            $relatedPosts = $relatedPosts->merge($tagRelated);
        }

        // Fill with latest posts as last resort
        if ($relatedPosts->count() < 3) {
            $exclude = $relatedPosts->pluck('id')->push($post->id);

            $latest = Post::published()
                ->whereNotIn('id', $exclude)
                ->with(['category', 'tags'])
                ->latest('published_at')
                ->take(3 - $relatedPosts->count())
                ->get();

            $relatedPosts = $relatedPosts->merge($latest);
        }

        $seoSource = $post;

        return view('blog.show', compact('post', 'relatedPosts', 'seoSource'));
    }

    public function category(Category $category): View
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
        $title = $category->name.' Articles';
        $description = "Articles about {$category->name} — Laravel development insights from Jeffrey Davidson.";

        if (! $posts->onFirstPage()) {
            $title .= " — Page {$posts->currentPage()}";
            $description .= " Page {$posts->currentPage()} of {$posts->lastPage()}.";
        }

        $seoSource = new SEOData(
            title: $title,
            description: $description,
            url: $canonicalUrl,
            canonical_url: $canonicalUrl,
        );

        return view('blog.category', compact('category', 'posts', 'seoSource'));
    }

    public function tag(Tag $tag): View
    {
        $posts = Post::published()
            ->withAnyTags([$tag])
            ->with(['category', 'author'])
            ->latest('published_at')
            ->paginate(10);

        abort_if($posts->currentPage() > $posts->lastPage(), 404);

        $canonicalUrl = $posts->onFirstPage()
            ? route('blog.tag', $tag)
            : route('blog.tag', ['tag' => $tag, 'page' => $posts->currentPage()]);
        $title = $tag->name.' Articles';
        $description = "Articles tagged with {$tag->name} on The Laravel Architect.";

        if (! $posts->onFirstPage()) {
            $title .= " — Page {$posts->currentPage()}";
            $description .= " Page {$posts->currentPage()} of {$posts->lastPage()}.";
        }

        $seoSource = new SEOData(
            title: $title,
            description: $description,
            url: $canonicalUrl,
            canonical_url: $canonicalUrl,
        );

        return view('blog.tag', compact('tag', 'posts', 'seoSource'));
    }
}
