<?php

declare(strict_types=1);

namespace App\Support\Seo;

use App\Models\Category;
use App\Models\Episode;
use App\Models\Podcast;
use App\Models\Post;
use App\Models\Project;
use App\Models\Tag;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Pagination\LengthAwarePaginator;
use RalphJSmit\Laravel\SEO\Support\SEOData;

final class CollectionSchemaBuilder
{
    /**
     * @param  list<array<string, mixed>>  $schemas
     * @param  array<string, mixed>  $pageData
     */
    public function add(array &$schemas, array $pageData, string $routeName): void
    {
        $seoSource = $this->seoSource($pageData);
        $collectionPage = null;
        $collectionItems = [];
        $positionOffset = 0;

        if ($routeName === 'blog.index' && ($posts = $this->posts($pageData)) instanceof LengthAwarePaginator) {
            $collectionPage = [
                'name' => ($selectedCategory = $this->category($pageData, 'selectedCategory')) instanceof Category
                    ? $selectedCategory->name.' Articles'
                    : 'Blog',
                'url' => $this->canonicalUrl($seoSource, route('blog.index')),
            ];
            $positionOffset = ($posts->currentPage() - 1) * $posts->perPage();

            foreach ($posts as $post) {
                $collectionItems[] = ['name' => $post->title, 'url' => route('blog.show', $post)];
            }
        } elseif ($routeName === 'blog.category'
            && ($posts = $this->posts($pageData)) instanceof LengthAwarePaginator
            && ($category = $this->category($pageData)) instanceof Category) {
            $collectionPage = [
                'name' => $category->name.' Articles',
                'url' => $this->canonicalUrl($seoSource, route('blog.category', $category)),
            ];
            $positionOffset = ($posts->currentPage() - 1) * $posts->perPage();

            foreach ($posts as $post) {
                $collectionItems[] = ['name' => $post->title, 'url' => route('blog.show', $post)];
            }
        } elseif ($routeName === 'blog.tag'
            && ($posts = $this->posts($pageData)) instanceof LengthAwarePaginator
            && ($tag = $this->tag($pageData)) instanceof Tag) {
            $collectionPage = [
                'name' => $tag->name.' Articles',
                'url' => $this->canonicalUrl($seoSource, route('blog.tag', $tag)),
            ];
            $positionOffset = ($posts->currentPage() - 1) * $posts->perPage();

            foreach ($posts as $post) {
                $collectionItems[] = ['name' => $post->title, 'url' => route('blog.show', $post)];
            }
        } elseif ($routeName === 'projects.index' && ($projects = $this->projects($pageData)) instanceof EloquentCollection) {
            $collectionPage = ['name' => 'Projects', 'url' => route('projects.index')];

            foreach ($projects as $project) {
                $collectionItems[] = ['name' => $project->title, 'url' => route('projects.show', $project)];
            }
        } elseif ($routeName === 'podcast.show'
            && ($podcast = $this->podcast($pageData)) instanceof Podcast
            && ($episodes = $this->episodes($pageData)) instanceof LengthAwarePaginator) {
            $collectionPage = [
                'name' => $podcast->name.' Episodes',
                'url' => $this->canonicalUrl($seoSource, route('podcast.show', $podcast)),
            ];
            $positionOffset = ($episodes->currentPage() - 1) * $episodes->perPage();

            foreach ($episodes as $episode) {
                $collectionItems[] = [
                    'name' => $episode->title,
                    'url' => route('podcast.episode', [$podcast, $episode]),
                ];
            }
        } elseif ($routeName === 'podcast.index') {
            $collectionPage = ['name' => 'Podcast', 'url' => route('podcast.index')];
            $podcast = $this->podcast($pageData);

            if ($podcast instanceof Podcast) {
                $collectionItems[] = ['name' => $podcast->name, 'url' => route('podcast.show', $podcast)];
            }
        } elseif ($routeName === 'archive.index' && ($items = $this->items($pageData)) instanceof LengthAwarePaginator) {
            $collectionPage = [
                'name' => 'Archive',
                'url' => $this->canonicalUrl($seoSource, route('archive.index')),
            ];
            $positionOffset = ($items->currentPage() - 1) * $items->perPage();

            foreach ($items as $item) {
                $collectionItems[] = ['name' => $item['title'], 'url' => $item['url']];
            }
        }

        if ($collectionPage === null) {
            return;
        }

        $itemListId = $collectionPage['url'].'#items';
        $schemas[] = [
            '@type' => 'CollectionPage',
            '@id' => $collectionPage['url'].'#collection',
            'name' => $collectionPage['name'],
            'url' => $collectionPage['url'],
            'mainEntity' => [
                '@type' => 'ItemList',
                '@id' => $itemListId,
            ],
        ];

        $itemListElements = [];
        foreach ($collectionItems as $index => $item) {
            $itemListElements[] = [
                '@type' => 'ListItem',
                'position' => $positionOffset + $index + 1,
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

    /**
     * @param  array<string, mixed>  $pageData
     */
    private function seoSource(array $pageData): ?SEOData
    {
        $value = $pageData['seoSource'] ?? null;

        return $value instanceof SEOData ? $value : null;
    }

    private function canonicalUrl(mixed $seoSource, string $fallback): string
    {
        return $seoSource instanceof SEOData && is_string($seoSource->canonical_url)
            ? $seoSource->canonical_url
            : $fallback;
    }

    /**
     * @param  array<string, mixed>  $pageData
     */
    private function category(array $pageData, string $key = 'category'): ?Category
    {
        $value = $pageData[$key] ?? null;

        return $value instanceof Category ? $value : null;
    }

    /**
     * @param  array<string, mixed>  $pageData
     * @return LengthAwarePaginator<int, Post>|null
     */
    private function posts(array $pageData): ?LengthAwarePaginator
    {
        $value = $pageData['posts'] ?? null;

        return $value instanceof LengthAwarePaginator ? $value : null;
    }

    /**
     * @param  array<string, mixed>  $pageData
     */
    private function tag(array $pageData): ?Tag
    {
        $value = $pageData['tag'] ?? null;

        return $value instanceof Tag ? $value : null;
    }

    /**
     * @param  array<string, mixed>  $pageData
     * @return EloquentCollection<int, Project>|null
     */
    private function projects(array $pageData): ?EloquentCollection
    {
        $value = $pageData['projects'] ?? null;

        return $value instanceof EloquentCollection ? $value : null;
    }

    /**
     * @param  array<string, mixed>  $pageData
     */
    private function podcast(array $pageData): ?Podcast
    {
        $value = $pageData['podcast'] ?? null;

        return $value instanceof Podcast ? $value : null;
    }

    /**
     * @param  array<string, mixed>  $pageData
     * @return LengthAwarePaginator<int, Episode>|null
     */
    private function episodes(array $pageData): ?LengthAwarePaginator
    {
        $value = $pageData['episodes'] ?? null;

        return $value instanceof LengthAwarePaginator ? $value : null;
    }

    /**
     * @param  array<string, mixed>  $pageData
     * @return LengthAwarePaginator<int, array{title: string, url: string}>|null
     */
    private function items(array $pageData): ?LengthAwarePaginator
    {
        $value = $pageData['items'] ?? null;

        return $value instanceof LengthAwarePaginator ? $value : null;
    }
}
