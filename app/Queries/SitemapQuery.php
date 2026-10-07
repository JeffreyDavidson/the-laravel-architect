<?php

declare(strict_types=1);

namespace App\Queries;

use App\Models\Category;
use App\Models\NewsletterIssue;
use App\Models\Podcast;
use App\Models\Post;
use App\Models\Project;
use Illuminate\Database\Eloquent\Collection;

/**
 * Reads the public content the sitemap lists, selecting only the columns its URLs and
 * modification dates need.
 */
final class SitemapQuery
{
    /**
     * Published posts with their tags, newest first.
     *
     * @return Collection<int, Post>
     */
    public function posts(): Collection
    {
        return Post::query()->published()
            ->select(['id', 'slug', 'category_id', 'published_at', 'updated_at'])
            ->with('tags')
            ->latest('published_at')
            ->get();
    }

    /**
     * Categories with at least one published post.
     *
     * @return Collection<int, Category>
     */
    public function categories(): Collection
    {
        return Category::query()
            ->whereHas('publishedPosts')
            ->get();
    }

    /**
     * Active podcasts with their published episodes.
     *
     * @return Collection<int, Podcast>
     */
    public function podcasts(): Collection
    {
        return Podcast::query()
            ->active()
            ->with('publishedEpisodes:id,podcast_id,slug,updated_at')
            ->get();
    }

    /** @return Collection<int, Project> */
    public function projects(): Collection
    {
        return Project::query()->published()
            ->get(['id', 'slug', 'updated_at']);
    }

    /** @return Collection<int, NewsletterIssue> */
    public function newsletterIssues(): Collection
    {
        return NewsletterIssue::query()->published()
            ->get(['id', 'slug', 'updated_at']);
    }
}
