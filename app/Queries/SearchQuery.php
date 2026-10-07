<?php

namespace App\Queries;

use App\Enums\SearchContentType;
use App\Models\Episode;
use App\Models\NewsletterIssue;
use App\Models\Podcast;
use App\Models\Post;
use App\Models\Project;
use App\Models\Video;
use App\Support\DisplayTimezone;
use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class SearchQuery
{
    /**
     * Each group pages independently through its own query parameter (for example
     * postsPage), keeping the search terms and returning to the group's heading. The page
     * size comes from `search.per_page`.
     *
     * @return array<string, LengthAwarePaginator<int, array{title: string, description: string|null, url: string, meta: string, external: bool}>>
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
            $results[$searchType->getLabel()] = $this->search($searchType, $like);
        }

        return $results;
    }

    /**
     * @return LengthAwarePaginator<int, array{title: string, description: string|null, url: string, meta: string, external: bool}>
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
                fn (Post $post): array => $this->postResult($post),
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
                fn (Project $project): array => $this->projectResult($project),
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
                fn (Podcast $podcast): array => $this->podcastResult($podcast),
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
                fn (NewsletterIssue $issue): array => $this->newsletterResult($issue),
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
                fn (Episode $episode): array => $this->episodeResult($episode),
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
                fn (Video $video): array => $this->videoResult($video),
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
     * @param  Closure(TModel): array{title: string, description: string|null, url: string, meta: string, external: bool}  $toResult
     * @return LengthAwarePaginator<int, array{title: string, description: string|null, url: string, meta: string, external: bool}>
     */
    private function paginate(Builder $query, string $pageName, SearchContentType $type, Closure $toResult): LengthAwarePaginator
    {
        $slug = Str::slug($type->getLabel());

        return $query
            ->paginate(config()->integer('search.per_page'), pageName: $pageName)
            ->withQueryString()
            ->fragment("search-{$slug}")
            ->through($toResult);
    }

    /** @return array{title: string, description: string|null, url: string, meta: string, external: bool} */
    private function postResult(Post $post): array
    {
        return [
            'title' => $post->title,
            'description' => $post->excerpt,
            'url' => route('blog.show', $post),
            'meta' => DisplayTimezone::convert($post->publishedAt())
                ?->format('M j, Y') ?? 'Article',
            'external' => false,
        ];
    }

    /** @return array{title: string, description: string|null, url: string, meta: string, external: bool} */
    private function projectResult(Project $project): array
    {
        return [
            'title' => $project->title,
            'description' => $project->description,
            'url' => route('projects.show', $project),
            'meta' => 'Project',
            'external' => false,
        ];
    }

    /** @return array{title: string, description: string|null, url: string, meta: string, external: bool} */
    private function podcastResult(Podcast $podcast): array
    {
        return [
            'title' => $podcast->name,
            'description' => $podcast->description,
            'url' => route('podcast.show', $podcast),
            'meta' => 'Podcast',
            'external' => false,
        ];
    }

    /** @return array{title: string, description: string|null, url: string, meta: string, external: bool} */
    private function episodeResult(Episode $episode): array
    {
        $podcast = $episode->podcast;

        if ($podcast === null) {
            throw new \UnexpectedValueException('Search result episode is missing its podcast.');
        }

        return [
            'title' => $episode->title,
            'description' => $episode->description,
            'url' => route('podcast.episode', [$podcast, $episode]),
            'meta' => DisplayTimezone::convert($episode->publishedAt())
                ?->format('M j, Y') ?? 'Episode',
            'external' => false,
        ];
    }

    /** @return array{title: string, description: string|null, url: string, meta: string, external: bool} */
    private function newsletterResult(NewsletterIssue $issue): array
    {
        return [
            'title' => $issue->title,
            'description' => $issue->excerpt,
            'url' => route('newsletter.issue', $issue),
            'meta' => DisplayTimezone::convert($issue->publishedAt())
                ?->format('M j, Y') ?? 'Newsletter',
            'external' => false,
        ];
    }

    /** @return array{title: string, description: string|null, url: string, meta: string, external: bool} */
    private function videoResult(Video $video): array
    {
        return [
            'title' => $video->title,
            'description' => $video->description,
            'url' => $video->youtube_url,
            'meta' => 'YouTube video',
            'external' => true,
        ];
    }
}
