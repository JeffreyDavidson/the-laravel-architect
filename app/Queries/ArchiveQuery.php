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
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ArchiveQuery
{
    private const int ITEMS_PER_PAGE = 18;

    /**
     * @return LengthAwarePaginator<int, array{type: string, typeLabel: string, title: string, summary: string|null, url: string, external: bool, date: string, dateTime: string}>
     */
    public function get(?SearchContentType $type = null, ?int $year = null): LengthAwarePaginator
    {
        $archiveQuery = $this->contentQueries($type);

        /** @var Builder<Model> $query */
        $query = array_shift($archiveQuery);

        foreach ($archiveQuery as $contentQuery) {
            $query->unionAll($contentQuery);
        }

        $query = DB::query()
            ->fromSub($query, 'archive')
            ->select(['id', 'type', 'title', 'summary', 'slug', 'podcast_slug', 'youtube_id', 'sort_date'])
            ->orderByDesc('sort_date')
            ->orderByDesc('id')
            ->orderBy('type');

        if ($year !== null) {
            $query->whereYear('sort_date', $year);
        }

        /** @var LengthAwarePaginator<int, object{ id: int, type: string, title: string, summary: string|null, slug: string, podcast_slug: string|null, youtube_id: string|null, sort_date: string }> $items */
        $items = $query->paginate(self::ITEMS_PER_PAGE)
            ->withQueryString();

        abort_if($items->currentPage() > $items->lastPage(), 404);

        return $items->through(fn (object $item): array => $this->record($item));
    }

    /**
     * @return list<int>
     */
    public function years(): array
    {
        $contentQueries = $this->contentQueries();
        /** @var Builder<Model> $query */
        $query = array_shift($contentQueries);

        foreach ($contentQueries as $contentQuery) {
            $query->unionAll($contentQuery);
        }

        $rawYears = DB::query()
            ->fromSub($query, 'archive')
            ->selectRaw("strftime('%Y', sort_date) as year")
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year')
            ->filter()
            ->all();

        $years = [];

        foreach ($rawYears as $year) {
            if (is_string($year) && ctype_digit($year)) {
                $years[] = (int) $year;
            }
        }

        rsort($years);

        return $years;
    }

    /**
     * @return list<Builder<Post>|Builder<Project>|Builder<Podcast>|Builder<NewsletterIssue>|Builder<Episode>|Builder<Video>>
     */
    private function contentQueries(?SearchContentType $type = null): array
    {
        $queries = [
            SearchContentType::Writing->value => Post::query()
                ->select(['id', 'title', 'excerpt as summary', 'slug'])
                ->selectRaw("'writing' as type, published_at as sort_date, NULL as podcast_slug, NULL as youtube_id")
                ->published(),
            SearchContentType::Projects->value => Project::query()
                ->select(['id', 'title', 'description as summary', 'slug'])
                ->selectRaw("'projects' as type, updated_at as sort_date, NULL as podcast_slug, NULL as youtube_id")
                ->published(),
            SearchContentType::Podcasts->value => Podcast::query()
                ->select(['id', 'name as title', 'description as summary', 'slug'])
                ->selectRaw("'podcasts' as type, updated_at as sort_date, NULL as podcast_slug, NULL as youtube_id")
                ->active(),
            SearchContentType::Newsletter->value => NewsletterIssue::query()
                ->select(['id', 'title', 'excerpt as summary', 'slug'])
                ->selectRaw("'newsletter' as type, published_at as sort_date, NULL as podcast_slug, NULL as youtube_id")
                ->published(),
            SearchContentType::Episodes->value => Episode::query()
                ->join('podcasts', 'podcasts.id', '=', 'episodes.podcast_id')
                ->where('podcasts.is_active', true)
                ->select(['episodes.id', 'episodes.title', 'episodes.description as summary', 'episodes.slug'])
                ->selectRaw("'episodes' as type, episodes.published_at as sort_date, podcasts.slug as podcast_slug, NULL as youtube_id")
                ->published(),
            SearchContentType::Videos->value => Video::query()
                ->select(['id', 'title', 'description as summary', 'slug'])
                ->selectRaw("'videos' as type, published_at as sort_date, NULL as podcast_slug, youtube_id")
                ->published(),
        ];

        if (! $type instanceof SearchContentType) {
            return array_values($queries);
        }

        return [$queries[$type->value]];
    }

    /**
     * @param  object{ id: int, type: string, title: string, summary: string|null, slug: string, podcast_slug: string|null, youtube_id: string|null, sort_date: string }  $item
     * @return array{type: string, typeLabel: string, title: string, summary: string|null, url: string, external: bool, date: string, dateTime: string}
     */
    private function record(object $item): array
    {
        $type = SearchContentType::from($item->type);
        $date = Carbon::parse($item->sort_date);

        $url = match ($type) {
            SearchContentType::Writing => route('blog.show', ['post' => $item->slug]),
            SearchContentType::Projects => route('projects.show', ['project' => $item->slug]),
            SearchContentType::Podcasts => route('podcast.show', ['podcast' => $item->slug]),
            SearchContentType::Newsletter => route('newsletter.issue', ['newsletterIssue' => $item->slug]),
            SearchContentType::Episodes => route('podcast.episode', ['podcast' => $item->podcast_slug, 'episode' => $item->slug]),
            SearchContentType::Videos => 'https://www.youtube.com/watch?v='.$item->youtube_id,
        };

        return [
            'type' => $type->value,
            'typeLabel' => $type->label(),
            'title' => $item->title,
            'summary' => $item->summary,
            'url' => $url,
            'external' => $type === SearchContentType::Videos,
            'date' => $date->format('F j, Y'),
            'dateTime' => $date->toDateString(),
        ];
    }
}
