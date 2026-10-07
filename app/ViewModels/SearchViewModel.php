<?php

declare(strict_types=1);

namespace App\ViewModels;

use App\Enums\SearchContentType;
use App\Models\Episode;
use App\Models\NewsletterIssue;
use App\Models\Podcast;
use App\Models\Post;
use App\Models\Project;
use App\Models\Video;
use App\Queries\SearchQuery;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use RalphJSmit\Laravel\SEO\Support\SEOData;
use UnexpectedValueException;

/**
 * @phpstan-import-type SearchResultPage from SearchQuery
 */
class SearchViewModel
{
    /**
     * @param  array<string, SearchResultPage>  $results  matching models keyed by content type value
     * @return array{
     *     query: string,
     *     results: array<string, LengthAwarePaginator<int, array{title: string, description: string|null, url: string, date: CarbonInterface|null, label: string, external: bool}>>,
     *     resultCount: int,
     *     typeOptions: array<string, string>,
     *     selectedType: string|null,
     *     seoSource: SEOData,
     * }
     */
    public function data(array $results, ?string $query, ?SearchContentType $selectedType = null): array
    {
        $query = trim($query ?? '');

        $results = array_map(
            fn (LengthAwarePaginator $group): LengthAwarePaginator => $group->through(fn (Model $model): array => $this->result($model)),
            $results,
        );

        return [
            'query' => $query,
            'results' => $results,
            'resultCount' => array_sum(array_map(fn (LengthAwarePaginator $group): int => $group->total(), $results)),
            'typeOptions' => SearchContentType::labels(),
            'selectedType' => $selectedType?->value,
            'seoSource' => new SEOData(
                title: $query === '' ? 'Search' : 'Search results',
                description: $query === ''
                    ? 'Search the writing, projects, podcasts, episodes, and videos from The Laravel Architect.'
                    : "Search results for {$query} on The Laravel Architect.",
                url: route('search'),
                robots: 'noindex, follow',
                canonical_url: route('search'),
            ),
        ];
    }

    /**
     * One search result as the page shows it: a plain-text description cut to 180 characters, and the
     * publication date, or the label shown in its place for undated content.
     *
     * @return array{title: string, description: string|null, url: string, date: CarbonInterface|null, label: string, external: bool}
     */
    private function result(Model $model): array
    {
        [$title, $description, $url, $date, $label] = match (true) {
            $model instanceof Post => [$model->title, $model->excerpt, route('blog.show', $model), $model->publishedAt(), 'Article'],
            $model instanceof Project => [$model->title, $model->description, route('projects.show', $model), null, 'Project'],
            $model instanceof Podcast => [$model->name, $model->description, route('podcast.show', $model), null, 'Podcast'],
            $model instanceof NewsletterIssue => [$model->title, $model->excerpt, route('newsletter.issue', $model), $model->publishedAt(), 'Newsletter'],
            $model instanceof Episode => [$model->title, $model->description, $this->episodeUrl($model), $model->publishedAt(), 'Episode'],
            $model instanceof Video => [$model->title, $model->description, $model->youtube_url, null, 'YouTube video'],
            default => throw new UnexpectedValueException('Unsupported search result model.'),
        };

        return [
            'title' => $title,
            'description' => $description === null ? null : Str::limit(strip_tags($description), 180),
            'url' => $url,
            'date' => $date,
            'label' => $label,
            'external' => $model instanceof Video,
        ];
    }

    private function episodeUrl(Episode $episode): string
    {
        $podcast = $episode->podcast;

        if ($podcast === null) {
            throw new UnexpectedValueException('Search result episode is missing its podcast.');
        }

        return route('podcast.episode', [$podcast, $episode]);
    }
}
