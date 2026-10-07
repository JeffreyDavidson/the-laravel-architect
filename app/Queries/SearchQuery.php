<?php

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

/**
 * @phpstan-type SearchResultPage LengthAwarePaginator<int, Post>|LengthAwarePaginator<int, Project>|LengthAwarePaginator<int, Podcast>|LengthAwarePaginator<int, NewsletterIssue>|LengthAwarePaginator<int, Episode>|LengthAwarePaginator<int, Video>
 */
class SearchQuery
{
    /**
     * Each group pages independently through its own query parameter (for example
     * postsPage), keeping the search terms and returning to the group's heading. The page
     * size comes from `search.per_page`. Groups are keyed by their content type value and
     * hold the matching published models.
     *
     * @return array<string, SearchResultPage>
     */
    public function get(?string $query, ?SearchContentType $type = null): array
    {
        $query = is_string($query) ? trim($query) : '';

        if ($query === '') {
            return [];
        }

        $like = '%'.addcslashes($query, '\\%_').'%';
        $results = [];

        foreach ($type instanceof SearchContentType ? [$type] : SearchContentType::cases() as $searchType) {
            $results[$searchType->value] = $this->search($searchType, $like);
        }

        return $results;
    }

    /**
     * @return SearchResultPage
     */
    private function search(SearchContentType $type, string $like): LengthAwarePaginator
    {
        return match ($type) {
            SearchContentType::Writing => $this->paginate(
                Post::query()
                    ->select(['id', 'title', 'slug', 'excerpt', 'published_at'])
                    ->published()
                    ->where(fn (Builder $postsQuery): Builder => $this
                        ->whereAnyLike($postsQuery, ['title', 'excerpt', 'content'], $like)
                        ->orWhereHas('tags', function (Builder $tagQuery) use ($like): void {
                            $tagQuery->whereRaw(
                                "json_extract(\"tags\".\"name\", ?) LIKE ? ESCAPE '\\'",
                                ['$.'.app()->getLocale(), $like],
                            );
                        }))
                    ->latest('published_at')
                    ->latest('id'),
                'postsPage',
                $type,
            ),
            SearchContentType::Projects => $this->paginate(
                Project::query()
                    ->select(['id', 'title', 'slug', 'description', 'sort_order', 'updated_at'])
                    ->published()
                    ->where(fn (Builder $projectsQuery): Builder => $this->whereAnyLike($projectsQuery, ['title', 'description', 'content'], $like))
                    ->orderBy('sort_order')
                    ->latest('updated_at')
                    ->latest('id'),
                'projectsPage',
                $type,
            ),
            SearchContentType::Podcasts => $this->paginate(
                Podcast::query()
                    ->select(['id', 'name', 'slug', 'description', 'long_description', 'sort_order'])
                    ->active()
                    ->where(fn (Builder $podcastsQuery): Builder => $this->whereAnyLike($podcastsQuery, ['name', 'description', 'long_description'], $like))
                    ->orderBy('sort_order')
                    ->orderBy('id'),
                'podcastsPage',
                $type,
            ),
            SearchContentType::Newsletter => $this->paginate(
                NewsletterIssue::query()
                    ->select(['id', 'title', 'slug', 'excerpt', 'content', 'published_at'])
                    ->published()
                    ->where(fn (Builder $issuesQuery): Builder => $this->whereAnyLike($issuesQuery, ['title', 'excerpt', 'content'], $like))
                    ->latest('published_at')
                    ->latest('id'),
                'newsletterPage',
                $type,
            ),
            SearchContentType::Episodes => $this->paginate(
                Episode::query()
                    ->select(['id', 'podcast_id', 'title', 'slug', 'description', 'published_at'])
                    ->published()
                    ->whereHas('podcast', function (Builder $podcastQuery): void {
                        $podcastQuery->active();
                    })
                    ->with('podcast:id,slug')
                    ->where(fn (Builder $episodesQuery): Builder => $this->whereAnyLike($episodesQuery, ['title', 'description', 'show_notes', 'transcript', 'guest_name'], $like))
                    ->latest('published_at')
                    ->latest('id'),
                'episodesPage',
                $type,
            ),
            SearchContentType::Videos => $this->paginate(
                Video::query()
                    ->select(['id', 'youtube_id', 'title', 'slug', 'description', 'published_at'])
                    ->published()
                    ->where(fn (Builder $videosQuery): Builder => $this->whereAnyLike($videosQuery, ['title', 'description'], $like))
                    ->latest('published_at')
                    ->latest('id'),
                'videosPage',
                $type,
            ),
        };
    }

    /**
     * Match the escaped pattern against any of the columns. Laravel's whereAny() does not
     * add the ESCAPE clause that SQLite needs for the backslash-escaped wildcards.
     *
     * @template TModel of Model
     *
     * @param  Builder<TModel>  $query
     * @param  list<literal-string>  $columns
     * @return Builder<TModel>
     */
    private function whereAnyLike(Builder $query, array $columns, string $like): Builder
    {
        foreach ($columns as $column) {
            $query->orWhereRaw("{$column} LIKE ? ESCAPE '\\'", [$like]);
        }

        return $query;
    }

    /**
     * @template TModel of Model
     *
     * @param  Builder<TModel>  $query
     * @return LengthAwarePaginator<int, TModel>
     */
    private function paginate(Builder $query, string $pageName, SearchContentType $type): LengthAwarePaginator
    {
        return $query
            ->paginate(config()->integer('search.per_page'), pageName: $pageName)
            ->withQueryString()
            ->fragment("search-{$type->value}");
    }
}
