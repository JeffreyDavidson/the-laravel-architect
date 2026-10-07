<?php

declare(strict_types=1);

namespace App\Support\Seo;

use App\Data\StructuredDataPage;
use App\Models\Category;
use App\Models\Episode;
use App\Models\Podcast;
use App\Models\Post;
use App\Models\Project;
use App\Models\Tag;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Pagination\LengthAwarePaginator;

final class CollectionSchemaBuilder
{
    /**
     * @param  list<array<string, mixed>>  $schemas
     */
    public function add(array &$schemas, StructuredDataPage $page): void
    {
        $listing = $this->listing($page);

        if (! $listing instanceof CollectionListing) {
            return;
        }

        $itemListId = "{$listing->url}#items";
        $schemas[] = [
            '@type' => 'CollectionPage',
            '@id' => "{$listing->url}#collection",
            'name' => $listing->name,
            'url' => $listing->url,
            'mainEntity' => [
                '@type' => 'ItemList',
                '@id' => $itemListId,
            ],
        ];

        $itemListElements = [];
        foreach ($listing->items as $index => $item) {
            $itemListElements[] = [
                '@type' => 'ListItem',
                'position' => $listing->positionOffset + $index + 1,
                'name' => $item['name'],
                'item' => $item['url'],
            ];
        }

        $schemas[] = [
            '@type' => 'ItemList',
            '@id' => $itemListId,
            'numberOfItems' => count($itemListElements),
            'itemListElement' => $itemListElements,
        ];
    }

    private function listing(StructuredDataPage $page): ?CollectionListing
    {
        $postItem = static fn (Post $post): array => ['name' => $post->title, 'url' => route('blog.show', $post)];

        return match (true) {
            $page->routeName === 'blog.index' && $page->posts instanceof LengthAwarePaginator => CollectionListing::paginated(
                $page->selectedCategory instanceof Category ? "{$page->selectedCategory->name} Articles" : 'Blog',
                $page->canonicalUrl(route('blog.index')),
                $page->posts,
                $postItem,
            ),
            $page->routeName === 'blog.category' && $page->posts instanceof LengthAwarePaginator && $page->category instanceof Category => CollectionListing::paginated(
                "{$page->category->name} Articles",
                $page->canonicalUrl(route('blog.category', $page->category)),
                $page->posts,
                $postItem,
            ),
            $page->routeName === 'blog.tag' && $page->posts instanceof LengthAwarePaginator && $page->tag instanceof Tag => CollectionListing::paginated(
                "Articles Tagged {$page->tag->name}",
                $page->canonicalUrl(route('blog.tag', $page->tag)),
                $page->posts,
                $postItem,
            ),
            $page->routeName === 'projects.index' && $page->projects instanceof EloquentCollection => new CollectionListing(
                'Projects',
                route('projects.index'),
                array_values(array_map(
                    static fn (Project $project): array => ['name' => $project->title, 'url' => route('projects.show', $project)],
                    $page->projects->all(),
                )),
            ),
            $page->routeName === 'podcast.show' && $page->podcast instanceof Podcast && $page->episodes instanceof LengthAwarePaginator => CollectionListing::paginated(
                "{$page->podcast->name} Episodes",
                $page->canonicalUrl(route('podcast.show', $page->podcast)),
                $page->episodes,
                fn (Episode $episode): array => [
                    'name' => $episode->title,
                    'url' => route('podcast.episode', [$page->podcast, $episode]),
                ],
            ),
            $page->routeName === 'podcast.index' => new CollectionListing(
                'Podcast',
                route('podcast.index'),
                $page->podcast instanceof Podcast
                    ? [['name' => $page->podcast->name, 'url' => route('podcast.show', $page->podcast)]]
                    : [],
            ),
            $page->routeName === 'archive.index' && $page->archiveItems instanceof LengthAwarePaginator => CollectionListing::paginated(
                'Archive',
                $page->canonicalUrl(route('archive.index')),
                $page->archiveItems,
                static fn (array $item): array => ['name' => $item['title'], 'url' => $item['url']],
            ),
            default => null,
        };
    }
}
