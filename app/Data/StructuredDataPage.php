<?php

declare(strict_types=1);

namespace App\Data;

use App\Models\Category;
use App\Models\Episode;
use App\Models\Podcast;
use App\Models\Post;
use App\Models\Project;
use App\Models\Tag;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Pagination\LengthAwarePaginator;
use RalphJSmit\Laravel\SEO\Support\SEOData;

/**
 * The page details the structured data builders read, mapped once from a page's view data.
 * Each value is null when the view data has no entry of the expected type under its key.
 */
final readonly class StructuredDataPage
{
    /**
     * @param  LengthAwarePaginator<int, Post>|null  $posts
     * @param  LengthAwarePaginator<int, Episode>|null  $episodes
     * @param  LengthAwarePaginator<int, array{title: string, url: string}>|null  $archiveItems
     * @param  EloquentCollection<int, Project>|null  $projects
     */
    public function __construct(
        public string $routeName,
        public ?SEOData $seoSource = null,
        public ?Post $post = null,
        public ?Category $category = null,
        public ?Category $selectedCategory = null,
        public ?Tag $tag = null,
        public ?Project $project = null,
        public ?Podcast $podcast = null,
        public ?Episode $episode = null,
        public ?LengthAwarePaginator $posts = null,
        public ?LengthAwarePaginator $episodes = null,
        public ?LengthAwarePaginator $archiveItems = null,
        public ?EloquentCollection $projects = null,
    ) {}

    /**
     * @param  array<string, mixed>  $data  The data a page's view model passes to its view.
     */
    public static function fromViewData(array $data, string $routeName): self
    {
        return new self(
            routeName: $routeName,
            seoSource: self::valueOfType($data, 'seoSource', SEOData::class),
            post: self::valueOfType($data, 'post', Post::class),
            category: self::valueOfType($data, 'category', Category::class),
            selectedCategory: self::valueOfType($data, 'selectedCategory', Category::class),
            tag: self::valueOfType($data, 'tag', Tag::class),
            project: self::valueOfType($data, 'project', Project::class),
            podcast: self::valueOfType($data, 'podcast', Podcast::class),
            episode: self::valueOfType($data, 'episode', Episode::class),
            posts: self::valueOfType($data, 'posts', LengthAwarePaginator::class),
            episodes: self::valueOfType($data, 'episodes', LengthAwarePaginator::class),
            archiveItems: self::valueOfType($data, 'items', LengthAwarePaginator::class),
            projects: self::valueOfType($data, 'projects', EloquentCollection::class),
        );
    }

    /** The page's canonical URL when its SEO data sets one, otherwise the given URL. */
    public function canonicalUrl(string $fallback): string
    {
        return $this->seoSource->canonical_url ?? $fallback;
    }

    /**
     * @template TValue of object
     *
     * @param  array<string, mixed>  $data
     * @param  class-string<TValue>  $type
     * @return TValue|null
     */
    private static function valueOfType(array $data, string $key, string $type): ?object
    {
        $value = $data[$key] ?? null;

        return $value instanceof $type ? $value : null;
    }
}
