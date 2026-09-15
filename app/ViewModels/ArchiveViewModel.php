<?php

namespace App\ViewModels;

use App\Enums\SearchContentType;
use Illuminate\Pagination\LengthAwarePaginator;
use RalphJSmit\Laravel\SEO\Support\SEOData;

class ArchiveViewModel
{
    /**
     * @param  LengthAwarePaginator<int, array{type: string, typeLabel: string, title: string, summary: string|null, url: string, external: bool, date: string, dateTime: string}>  $items
     * @param  list<int>  $years
     * @return array{items: LengthAwarePaginator<int, array{type: string, typeLabel: string, title: string, summary: string|null, url: string, external: bool, date: string, dateTime: string}>, typeOptions: array<string, string>, selectedType: string|null, yearOptions: array<int, int>, selectedYear: int|null, seoSource: SEOData}
     */
    public function data(LengthAwarePaginator $items, array $years, ?SearchContentType $selectedType = null, ?int $selectedYear = null): array
    {
        $parameters = array_filter([
            'type' => $selectedType?->value,
            'year' => $selectedYear,
            'page' => $items->onFirstPage() ? null : $items->currentPage(),
        ], fn (mixed $value): bool => $value !== null);
        $url = route('archive.index', $parameters);

        return [
            'items' => $items,
            'years' => $years,
            'typeOptions' => SearchContentType::labels(),
            'selectedType' => $selectedType?->value,
            'yearOptions' => array_combine($years, $years),
            'selectedYear' => $selectedYear,
            'seoSource' => new SEOData(
                title: $selectedType === null && $selectedYear === null ? 'Archive' : 'Archive results',
                description: 'Browse writing, projects, podcasts, episodes, newsletters, and videos from The Laravel Architect.',
                url: $url,
                canonical_url: $url,
            ),
        ];
    }
}
