<?php

declare(strict_types=1);

namespace App\ViewModels;

use App\Models\Post;
use App\Models\Tag;
use Illuminate\Pagination\LengthAwarePaginator;
use JeffreyDavidson\CreatorKit\Contracts\PageViewModel;
use JeffreyDavidson\CreatorKit\Data\PageMeta;
use JeffreyDavidson\CreatorKit\Support\Seo\CollectionListing;
use JeffreyDavidson\CreatorKit\Support\Seo\JsonLd;
use JeffreyDavidson\CreatorKit\Support\Seo\PaginatedPageSeo;
use RalphJSmit\Laravel\SEO\Support\SEOData;

final readonly class BlogTagViewModel implements PageViewModel
{
    public function __construct(private SiteStructuredData $site) {}

    /**
     * @return array{
     *     tag: Tag,
     *     posts: LengthAwarePaginator<int, Post>,
     *     pageMeta: PageMeta,
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
        abort_if($page->isOutOfRange(), 404);
        $canonicalUrl = $page->url('blog.tag', ['tag' => $tag]);

        return [
            'tag' => $tag,
            'posts' => $posts,
            'pageMeta' => new PageMeta(
                seo: new SEOData(
                    title: $page->title("Articles Tagged {$tag->name}"),
                    description: $page->description("Articles tagged with {$tag->name} on The Laravel Architect."),
                    url: $canonicalUrl,
                    canonical_url: $canonicalUrl,
                ),
                structuredData: [
                    ...JsonLd::collectionPage(CollectionListing::paginated(
                        "Articles Tagged {$tag->name}",
                        $canonicalUrl,
                        $posts,
                        static fn (Post $post): array => ['name' => $post->title, 'url' => route('blog.show', $post)],
                    )),
                    $this->site->breadcrumbs([
                        ['name' => 'Blog', 'url' => route('blog.index')],
                        ['name' => $tag->name, 'url' => $canonicalUrl],
                    ]),
                ],
            ),
        ];
    }
}
