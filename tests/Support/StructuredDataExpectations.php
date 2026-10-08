<?php

namespace Tests\Support;

use App\Data\PageMeta;
use App\ViewModels\SiteStructuredData;
use Illuminate\Support\Facades\URL;

/**
 * Expected JSON-LD nodes for the structured data tests. Generated URLs use the fixed origin
 * https://example.test (see useFixedOrigin()), so the expectations do not depend on APP_URL.
 */
class StructuredDataExpectations
{
    public static function useFixedOrigin(): void
    {
        URL::forceRootUrl('https://example.test');
        URL::forceScheme('https');
    }

    /**
     * The page's full JSON-LD graph, as the site layout renders it.
     *
     * @return list<array<string, mixed>>
     */
    public static function graph(PageMeta $pageMeta): array
    {
        return app(SiteStructuredData::class)->graph($pageMeta);
    }

    /** @return array<string, mixed> */
    public static function website(): array
    {
        return [
            '@type' => 'WebSite',
            '@id' => 'https://example.test#website',
            'name' => 'The Laravel Architect',
            'url' => 'https://example.test',
            'author' => [
                '@type' => 'Person',
                '@id' => 'https://example.test/about#person',
                'name' => 'Jeffrey Davidson',
                'url' => 'https://example.test/about',
            ],
        ];
    }

    /** @return array{'@type': string, '@id': string} */
    public static function author(): array
    {
        return [
            '@type' => 'Person',
            '@id' => 'https://example.test/about#person',
        ];
    }

    /**
     * @param  list<array{0: string, 1: string}>  $items
     * @return array<string, mixed>
     */
    public static function breadcrumbs(array $items): array
    {
        return [
            '@type' => 'BreadcrumbList',
            'itemListElement' => array_map(
                fn (array $item, int $index): array => [
                    '@type' => 'ListItem',
                    'position' => $index + 1,
                    'name' => $item[0],
                    'item' => $item[1],
                ],
                $items,
                array_keys($items),
            ),
        ];
    }

    /**
     * @param  array<int, array{0: string, 1: string}>  $itemsByPosition
     * @return list<array<string, mixed>>
     */
    public static function collection(string $name, string $url, array $itemsByPosition): array
    {
        $elements = [];

        foreach ($itemsByPosition as $position => $item) {
            $elements[] = [
                '@type' => 'ListItem',
                'position' => $position,
                'name' => $item[0],
                'item' => $item[1],
            ];
        }

        return [
            [
                '@type' => 'CollectionPage',
                '@id' => "{$url}#collection",
                'name' => $name,
                'url' => $url,
                'mainEntity' => [
                    '@type' => 'ItemList',
                    '@id' => "{$url}#items",
                ],
            ],
            [
                '@type' => 'ItemList',
                '@id' => "{$url}#items",
                'numberOfItems' => count($elements),
                'itemListElement' => $elements,
            ],
        ];
    }

    /**
     * A fixed page of the site as part of the website.
     *
     * @return array<string, mixed>
     */
    public static function page(string $type, string $name, string $url, bool $isProfile = false): array
    {
        $page = [
            '@type' => $type,
            '@id' => "{$url}#page",
            'name' => $name,
            'url' => $url,
            'isPartOf' => [
                '@type' => 'WebSite',
                '@id' => 'https://example.test#website',
            ],
        ];

        if ($isProfile) {
            $page['mainEntity'] = self::author();
        }

        return $page;
    }
}
