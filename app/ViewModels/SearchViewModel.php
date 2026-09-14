<?php

namespace App\ViewModels;

use RalphJSmit\Laravel\SEO\Support\SEOData;

class SearchViewModel
{
    /**
     * @param  array<string, array<int, array{title: string, description: string|null, url: string, meta: string, external: bool}>>  $results
     * @return array{
     *     query: string,
     *     results: array<string, array<int, array{title: string, description: string|null, url: string, meta: string, external: bool}>>,
     *     resultCount: int,
     *     seoSource: SEOData,
     * }
     */
    public function data(array $results, ?string $query): array
    {
        $query = trim($query ?? '');

        return [
            'query' => $query,
            'results' => $results,
            'resultCount' => array_sum(array_map('count', $results)),
            'seoSource' => new SEOData(
                title: $query === '' ? 'Search' : 'Search results',
                description: $query === ''
                    ? 'Search the writing, projects, podcasts, episodes, and videos from The Laravel Architect.'
                    : "Search results for {$query} on The Laravel Architect.",
                url: route('search'),
                canonical_url: route('search'),
                robots: 'noindex, follow',
            ),
        ];
    }
}
