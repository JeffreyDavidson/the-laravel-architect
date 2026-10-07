<?php

namespace App\ViewModels;

use App\Models\Post;
use App\Models\Tag;
use App\Support\Seo\PaginatedPageSeo;
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
        $posts = Post::query()->published()
            ->withAnyTags([$tag])
            ->with(['category', 'author'])
            ->latest('published_at')
            ->latest('id')
            ->paginate(10);

        $page = PaginatedPageSeo::forCurrentPage($posts);
        $canonicalUrl = $page->url('blog.tag', ['tag' => $tag]);

        return [
            'tag' => $tag,
            'posts' => $posts,
            'seoSource' => new SEOData(
                title: $page->title("Articles Tagged {$tag->name}"),
                description: $page->description("Articles tagged with {$tag->name} on The Laravel Architect."),
                url: $canonicalUrl,
                canonical_url: $canonicalUrl,
            ),
        ];
    }
}
