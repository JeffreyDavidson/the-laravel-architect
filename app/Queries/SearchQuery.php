<?php

namespace App\Queries;

use App\Enums\SearchContentType;
use App\Models\Episode;
use App\Models\NewsletterIssue;
use App\Models\Podcast;
use App\Models\Post;
use App\Models\Project;
use App\Models\Video;
use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class SearchQuery
{
    public const int PER_PAGE = 12;

    /**
     * Each group pages independently through its own query parameter (for example
     * postsPage), keeping the search terms and returning to the group's heading.
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

        $searches = [
            'Writing' => fn (): LengthAwarePaginator => $this->paginate(
                Post::query()
                    ->select(['id', 'title', 'slug', 'excerpt', 'published_at'])
                    ->published()
                    ->where(function (Builder $postsQuery) use ($like): void {
                        $postsQuery
                            ->whereRaw("title LIKE ? ESCAPE '\\'", [$like])
                            ->orWhereRaw("excerpt LIKE ? ESCAPE '\\'", [$like])
                            ->orWhereRaw("content LIKE ? ESCAPE '\\'", [$like])
                            ->orWhereHas('tags', function (Builder $tagQuery) use ($like): void {
                                $tagQuery->whereRaw(
                                    "json_extract(\"tags\".\"name\", ?) LIKE ? ESCAPE '\\'",
                                    ['$.'.app()->getLocale(), $like],
                                );
                            });
                    })
                    ->latest('published_at')
                    ->latest('id'),
                'postsPage',
                'Writing',
                fn (Post $post): array => $this->postResult($post),
            ),
            'Projects' => fn (): LengthAwarePaginator => $this->paginate(
                Project::query()
                    ->select(['id', 'title', 'slug', 'description', 'sort_order', 'updated_at'])
                    ->published()
                    ->where(function (Builder $projectsQuery) use ($like): void {
                        $projectsQuery
                            ->whereRaw("title LIKE ? ESCAPE '\\'", [$like])
                            ->orWhereRaw("description LIKE ? ESCAPE '\\'", [$like])
                            ->orWhereRaw("content LIKE ? ESCAPE '\\'", [$like]);
                    })
                    ->orderBy('sort_order')
                    ->latest('updated_at')
                    ->latest('id'),
                'projectsPage',
                'Projects',
                fn (Project $project): array => $this->projectResult($project),
            ),
            'Podcasts' => fn (): LengthAwarePaginator => $this->paginate(
                Podcast::query()
                    ->select(['id', 'name', 'slug', 'description', 'long_description', 'sort_order'])
                    ->active()
                    ->where(function (Builder $podcastsQuery) use ($like): void {
                        $podcastsQuery
                            ->whereRaw("name LIKE ? ESCAPE '\\'", [$like])
                            ->orWhereRaw("description LIKE ? ESCAPE '\\'", [$like])
                            ->orWhereRaw("long_description LIKE ? ESCAPE '\\'", [$like]);
                    })
                    ->orderBy('sort_order')
                    ->orderBy('id'),
                'podcastsPage',
                'Podcasts',
                fn (Podcast $podcast): array => $this->podcastResult($podcast),
            ),
            'Newsletter' => fn (): LengthAwarePaginator => $this->paginate(
                NewsletterIssue::query()
                    ->select(['id', 'title', 'slug', 'excerpt', 'content', 'published_at'])
                    ->published()
                    ->where(function (Builder $issuesQuery) use ($like): void {
                        $issuesQuery
                            ->whereRaw("title LIKE ? ESCAPE '\\'", [$like])
                            ->orWhereRaw("excerpt LIKE ? ESCAPE '\\'", [$like])
                            ->orWhereRaw("content LIKE ? ESCAPE '\\'", [$like]);
                    })
                    ->latest('published_at')
                    ->latest('id'),
                'newsletterPage',
                'Newsletter',
                fn (NewsletterIssue $issue): array => $this->newsletterResult($issue),
            ),
            'Episodes' => fn (): LengthAwarePaginator => $this->paginate(
                Episode::query()
                    ->select(['id', 'podcast_id', 'title', 'slug', 'description', 'published_at'])
                    ->published()
                    ->whereHas('podcast', function (Builder $podcastQuery): void {
                        $podcastQuery->active();
                    })
                    ->with('podcast:id,slug')
                    ->where(function (Builder $episodesQuery) use ($like): void {
                        $episodesQuery
                            ->whereRaw("title LIKE ? ESCAPE '\\'", [$like])
                            ->orWhereRaw("description LIKE ? ESCAPE '\\'", [$like])
                            ->orWhereRaw("show_notes LIKE ? ESCAPE '\\'", [$like])
                            ->orWhereRaw("transcript LIKE ? ESCAPE '\\'", [$like])
                            ->orWhereRaw("guest_name LIKE ? ESCAPE '\\'", [$like]);
                    })
                    ->latest('published_at')
                    ->latest('id'),
                'episodesPage',
                'Episodes',
                fn (Episode $episode): array => $this->episodeResult($episode),
            ),
            'Videos' => fn (): LengthAwarePaginator => $this->paginate(
                Video::query()
                    ->select(['id', 'youtube_id', 'title', 'slug', 'description', 'published_at'])
                    ->published()
                    ->where(function (Builder $videosQuery) use ($like): void {
                        $videosQuery
                            ->whereRaw("title LIKE ? ESCAPE '\\'", [$like])
                            ->orWhereRaw("description LIKE ? ESCAPE '\\'", [$like]);
                    })
                    ->latest('published_at')
                    ->latest('id'),
                'videosPage',
                'Videos',
                fn (Video $video): array => $this->videoResult($video),
            ),
        ];

        if ($type instanceof SearchContentType) {
            $searches = [$type->label() => $searches[$type->label()]];
        }

        return array_map(fn (callable $search): LengthAwarePaginator => $search(), $searches);
    }

    /**
     * @template TModel of Model
     *
     * @param  Builder<TModel>  $query
     * @param  Closure(TModel): array{title: string, description: string|null, url: string, meta: string, external: bool}  $toResult
     * @return LengthAwarePaginator<int, array{title: string, description: string|null, url: string, meta: string, external: bool}>
     */
    private function paginate(Builder $query, string $pageName, string $group, Closure $toResult): LengthAwarePaginator
    {
        $slug = Str::slug($group);

        return $query
            ->paginate(self::PER_PAGE, pageName: $pageName)
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
            'meta' => $post->publishedAt()
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
            'meta' => $episode->publishedAt()
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
            'meta' => $issue->publishedAt()
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
