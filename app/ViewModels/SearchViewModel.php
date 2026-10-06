<?php

namespace App\ViewModels;

use App\Enums\SearchContentType;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use RalphJSmit\Laravel\SEO\Support\SEOData;

class SearchViewModel
{
    /**
     * @param  array<string, LengthAwarePaginator<int, array{title: string, description: string|null, url: string, meta: string, external: bool}>>  $results
     * @return array{
     *     query: string,
     *     results: array<string, LengthAwarePaginator<int, array{title: string, description: string|null, highlightedTitle: string, highlightedDescription: string|null, url: string, meta: string, external: bool}>>,
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
     * @param  array<string, LengthAwarePaginator<int, array{title: string, description: string|null, url: string, meta: string, external: bool}>>  $results
     * @return array<string, LengthAwarePaginator<int, array{title: string, description: string|null, highlightedTitle: string, highlightedDescription: string|null, url: string, meta: string, external: bool}>>
     */
    private function highlightResults(array $results, string $query): array
    {
        return array_map(
            fn (LengthAwarePaginator $group): LengthAwarePaginator => $group->through(function (array $item) use ($query): array {
                $description = $item['description'] === null
                    ? null
                    : Str::limit(strip_tags($item['description']), 180);

                return [
                    'title' => $item['title'],
                    'description' => $item['description'],
                    'highlightedTitle' => $this->highlight($item['title'], $query),
                    'highlightedDescription' => $description === null ? null : $this->highlight($description, $query),
                    'url' => $item['url'],
                    'meta' => $item['meta'],
                    'external' => $item['external'],
                ];
            }),
            $results,
        );
    }

    /**
     * Wrap each match of the query in a mark element, matching against the raw
     * text and escaping every segment, so a match can never split an entity
     * such as `&amp;` and the result stays safe to print unescaped.
     */
    private function highlight(string $value, string $query): string
    {
        if ($query === '') {
            return e($value);
        }

        $quotedQuery = preg_quote($query, '/');
        $segments = preg_split("/({$quotedQuery})/iu", $value, flags: PREG_SPLIT_DELIM_CAPTURE);

        if ($segments === false) {
            return e($value);
        }

        $highlightedSegments = array_map(
            fn (string $segment, int $index): string => $index % 2 === 1
                ? '<mark class="rounded bg-brand-100 px-0.5 text-inherit dark:bg-brand-800">'.e($segment).'</mark>'
                : e($segment),
            $segments,
            array_keys($segments),
        );

        return implode('', $highlightedSegments);
    }
}
