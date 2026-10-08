<?php

declare(strict_types=1);

namespace App\ViewModels;

use App\Data\ContentListItem;
use App\Enums\SearchContentType;
use App\Queries\ArchiveQuery;
use App\Support\Seo\PaginatedPageSeo;
use Carbon\CarbonImmutable;
use Illuminate\Pagination\LengthAwarePaginator;
use RalphJSmit\Laravel\SEO\Support\SEOData;

final readonly class ArchiveViewModel
{
    public function __construct(private ArchiveQuery $archiveQuery) {}

    /**
     * The archive page for the validated filters. Page links keep the request's query string, and a
     * page past the last one is a 404.
     *
     * @return array{items: LengthAwarePaginator<int, array{typeLabel: string, title: string, summary: string|null, url: string, external: bool, date: CarbonImmutable}>, years: list<int>, typeOptions: array<string, string>, selectedType: string|null, yearOptions: array<int, int>, selectedYear: int|null, seoSource: SEOData}
     */
    public function data(?SearchContentType $selectedType = null, ?int $selectedYear = null): array
    {
        $items = $this->archiveQuery->get($selectedType, $selectedYear)
            ->withQueryString();
        $years = $this->archiveQuery->years();
        $page = PaginatedPageSeo::forCurrentPage($items);
        abort_if($page->isOutOfRange(), 404);

        $url = $page->url('archive.index', array_filter([
            'type' => $selectedType?->value,
            'year' => $selectedYear,
        ], fn (mixed $value): bool => $value !== null));
        $items->through(fn (ContentListItem $item): array => $this->listing($item));

        return [
            'items' => $items,
            'years' => $years,
            'typeOptions' => SearchContentType::labels(),
            'selectedType' => $selectedType?->value,
            'yearOptions' => array_combine($years, $years),
            'selectedYear' => $selectedYear,
            'seoSource' => new SEOData(
                title: ! $selectedType instanceof SearchContentType && $selectedYear === null ? 'Archive' : 'Archive results',
                description: 'Browse writing, projects, podcasts, episodes, newsletters, and videos from The Laravel Architect.',
                url: $url,
                canonical_url: $url,
            ),
        ];
    }

    /**
     * @return array{typeLabel: string, title: string, summary: string|null, url: string, external: bool, date: CarbonImmutable}
     */
    private function listing(ContentListItem $item): array
    {
        return [
            'typeLabel' => $item->type->getLabel(),
            'title' => $item->title,
            'summary' => $item->summary,
            'url' => match ($item->type) {
                SearchContentType::Writing => route('blog.show', ['post' => $item->slug]),
                SearchContentType::Projects => route('projects.show', ['project' => $item->slug]),
                SearchContentType::Podcasts => route('podcast.show', ['podcast' => $item->slug]),
                SearchContentType::Newsletter => route('newsletter.issue', ['newsletterIssue' => $item->slug]),
                SearchContentType::Episodes => route('podcast.episode', ['podcast' => $item->podcastSlug, 'episode' => $item->slug]),
                SearchContentType::Videos => "https://www.youtube.com/watch?v={$item->youtubeId}",
            },
            'external' => $item->type === SearchContentType::Videos,
            'date' => $item->date,
        ];
    }
}
