<?php

declare(strict_types=1);

namespace App\Queries;

use App\Enums\SearchContentType;
use App\Models\Episode;
use App\Models\NewsletterIssue;
use App\Models\Podcast;
use App\Models\Post;
use App\Models\Project;
use App\Models\Video;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use JeffreyDavidson\CreatorKit\Support\Search\TextSearch;

/**
 * @phpstan-type SearchResultPage LengthAwarePaginator<int, Post>|LengthAwarePaginator<int, Project>|LengthAwarePaginator<int, Podcast>|LengthAwarePaginator<int, NewsletterIssue>|LengthAwarePaginator<int, Episode>|LengthAwarePaginator<int, Video>
 */
final class SearchQuery
{
    /**
     * Each group pages independently through its own query parameter (for example
     * postsPage). The page size comes from `search.per_page`. Groups are keyed by their
     * content type value and hold the matching published models.
     *
     * @return array<string, SearchResultPage>
     */
    public function get(?string $query, ?SearchContentType $type = null): array
    {
        $query = is_string($query) ? trim($query) : '';

        if ($query === '') {
            return [];
        }

        $results = [];

        foreach ($type instanceof SearchContentType ? [$type] : SearchContentType::cases() as $searchType) {
            $results[$searchType->value] = $this->search($searchType, $query);
        }

        return $results;
    }

    /**
     * @return SearchResultPage
     */
    private function search(SearchContentType $type, string $term): LengthAwarePaginator
    {
        return match ($type) {
            SearchContentType::Writing => $this->paginate(
                Post::query()
                    ->select(['id', 'title', 'slug', 'excerpt', 'published_at'])
                    ->published()
                    ->where(function (Builder $postsQuery) use ($term): void {
                        TextSearch::constrain($postsQuery, ['title', 'excerpt', 'content'], $term);
                        $postsQuery->orWhereHas('tags', function (Builder $tagQuery) use ($term): void {
                            $escapedTerm = addcslashes($term, '\\%_');
                            $tagQuery->whereRaw(
                                "json_extract(\"tags\".\"name\", ?) LIKE ? ESCAPE '\\'",
                                ['$.'.app()->getLocale(), "%{$escapedTerm}%"],
                            );
                        });
                    })
                    ->latest('published_at')
                    ->latest('id'),
                'postsPage',
            ),
            SearchContentType::Projects => $this->paginate(
                Project::query()
                    ->select(['id', 'title', 'slug', 'description', 'sort_order', 'updated_at'])
                    ->published()
                    ->where(fn (Builder $projectsQuery) => TextSearch::constrain($projectsQuery, ['title', 'description', 'content'], $term))
                    ->orderBy('sort_order')
                    ->latest('updated_at')
                    ->latest('id'),
                'projectsPage',
            ),
            SearchContentType::Podcasts => $this->paginate(
                Podcast::query()
                    ->select(['id', 'name', 'slug', 'description', 'long_description', 'sort_order'])
                    ->active()
                    ->where(fn (Builder $podcastsQuery) => TextSearch::constrain($podcastsQuery, ['name', 'description', 'long_description'], $term))
                    ->orderBy('sort_order')
                    ->orderBy('id'),
                'podcastsPage',
            ),
            SearchContentType::Newsletter => $this->paginate(
                NewsletterIssue::query()
                    ->select(['id', 'title', 'slug', 'excerpt', 'content', 'published_at'])
                    ->published()
                    ->where(fn (Builder $issuesQuery) => TextSearch::constrain($issuesQuery, ['title', 'excerpt', 'content'], $term))
                    ->latest('published_at')
                    ->latest('id'),
                'newsletterPage',
            ),
            SearchContentType::Episodes => $this->paginate(
                Episode::query()
                    ->select(['id', 'podcast_id', 'title', 'slug', 'description', 'published_at'])
                    ->published()
                    ->whereHas('podcast', function (Builder $podcastQuery): void {
                        $podcastQuery->active();
                    })
                    ->with('podcast:id,slug')
                    ->where(fn (Builder $episodesQuery) => TextSearch::constrain($episodesQuery, ['title', 'description', 'show_notes', 'transcript', 'guest_name'], $term))
                    ->latest('published_at')
                    ->latest('id'),
                'episodesPage',
            ),
            SearchContentType::Videos => $this->paginate(
                Video::query()
                    ->select(['id', 'youtube_id', 'title', 'slug', 'description', 'published_at'])
                    ->published()
                    ->where(fn (Builder $videosQuery) => TextSearch::constrain($videosQuery, ['title', 'description'], $term))
                    ->latest('published_at')
                    ->latest('id'),
                'videosPage',
            ),
        };
    }

    /**
     * @template TModel of Model
     *
     * @param  Builder<TModel>  $query
     * @return LengthAwarePaginator<int, TModel>
     */
    private function paginate(Builder $query, string $pageName): LengthAwarePaginator
    {
        return $query->paginate(config()->integer('search.per_page'), pageName: $pageName);
    }
}
