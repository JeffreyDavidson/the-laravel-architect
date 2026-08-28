<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Tag;
use Illuminate\Contracts\View\View;
use RalphJSmit\Laravel\SEO\Support\SEOData;

class ShowBlogTagController extends Controller
{
    public function __invoke(Tag $tag): View
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
