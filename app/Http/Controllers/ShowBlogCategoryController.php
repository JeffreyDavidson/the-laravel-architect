<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Contracts\View\View;
use RalphJSmit\Laravel\SEO\Support\SEOData;

class ShowBlogCategoryController extends Controller
{
    public function __invoke(Category $category): View
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
}
