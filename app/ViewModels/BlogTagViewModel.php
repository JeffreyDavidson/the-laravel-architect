<?php

namespace App\ViewModels;

use App\Models\Post;
use App\Models\Tag;
use Illuminate\Pagination\LengthAwarePaginator;
use RalphJSmit\Laravel\SEO\Support\SEOData;

class BlogTagViewModel
{
    /**
     * @return array{
     *     tag: Tag,
     *     posts: LengthAwarePaginator<int, Post>,
     *     seoSource: SEOData,
     * }
     */
    public function data(Tag $tag): array
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
        $title = "{$tag->name} Articles";
        $description = "Articles tagged with {$tag->name} on The Laravel Architect.";

        if (! $posts->onFirstPage()) {
            $title .= " — Page {$posts->currentPage()}";
            $description .= " Page {$posts->currentPage()} of {$posts->lastPage()}.";
        }

        return [
            'tag' => $tag,
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
