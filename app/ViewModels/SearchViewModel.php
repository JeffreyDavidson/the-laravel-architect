<?php

namespace App\ViewModels;

use App\Enums\SearchContentType;
use Illuminate\Support\Str;
use RalphJSmit\Laravel\SEO\Support\SEOData;

class SearchViewModel
{
    /**
     * @param  array<string, array<int, array{title: string, description: string|null, url: string, meta: string, external: bool}>>  $results
     * @return array{
     *     query: string,
     *     results: array<string, array<int, array{title: string, description: string|null, highlightedTitle: string, highlightedDescription: string|null, url: string, meta: string, external: bool}>>,
     *     resultCount: int,
     *     typeOptions: array<string, string>,
     *     selectedType: string|null,
     *     seoSource: SEOData,
     * }
     */
    public function data(array $results, ?string $query, ?SearchContentType $selectedType = null): array
    {
        $query = trim($query ?? '');
        $results = $this->highlightResults($results, $query);

        return [
            'query' => $query,
            'results' => $results,
            'resultCount' => array_sum(array_map(count(...), $results)),
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
     * @param  array<string, array<int, array{title: string, description: string|null, url: string, meta: string, external: bool}>>  $results
     * @return array<string, array<int, array{title: string, description: string|null, highlightedTitle: string, highlightedDescription: string|null, url: string, meta: string, external: bool}>>
     */
    private function highlightResults(array $results, string $query): array
    {
        $highlightedResults = [];

        foreach ($results as $type => $items) {
            $highlightedResults[$type] = array_map(function (array $item) use ($query): array {
                $description = $item['description'] === null
                    ? null
                    : Str::limit(strip_tags($item['description']), 180);

                return [
                    ...$item,
                    'highlightedTitle' => $this->highlight($item['title'], $query),
                    'highlightedDescription' => $description === null ? null : $this->highlight($description, $query),
                ];
            }, $items);
        }

        return $highlightedResults;
    }

    private function highlight(string $value, string $query): string
    {
        $escapedValue = e($value);

        if ($query === '') {
            return $escapedValue;
        }

        return preg_replace(
            '/'.preg_quote(e($query), '/').'/iu',
            '<mark class="rounded bg-brand-100 px-0.5 text-inherit dark:bg-brand-800">$0</mark>',
            $escapedValue,
        ) ?? $escapedValue;
    }
}
