<?php

declare(strict_types=1);

namespace App\Support\Seo;

/**
 * Generic schema.org JSON-LD shapes built from plain names and URLs. They know nothing about
 * this site's models or routes; callers pass the values in.
 */
final class JsonLd
{
    /**
     * A BreadcrumbList with one ListItem per crumb, in order and numbered from one.
     *
     * @param  list<array{name: string, url: string}>  $crumbs
     * @return array<string, mixed>
     */
    public static function breadcrumbList(array $crumbs): array
    {
        return [
            '@type' => 'BreadcrumbList',
            'itemListElement' => array_map(
                fn (array $crumb, int $index): array => [
                    '@type' => 'ListItem',
                    'position' => $index + 1,
                    'name' => $crumb['name'],
                    'item' => $crumb['url'],
                ],
                $crumbs,
                array_keys($crumbs),
            ),
        ];
    }

    /**
     * A CollectionPage and the ItemList it points at. Item positions continue from the listing's
     * offset, so a later page of a paginated listing does not start again at one.
     *
     * @return list<array<string, mixed>>
     */
    public static function collectionPage(CollectionListing $listing): array
    {
        $itemListId = "{$listing->url}#items";
        $itemListElements = [];

        foreach ($listing->items as $index => $item) {
            $itemListElements[] = [
                '@type' => 'ListItem',
                'position' => $listing->positionOffset + $index + 1,
                'name' => $item['name'],
                'item' => $item['url'],
            ];
        }

        return [
            [
                '@type' => 'CollectionPage',
                '@id' => "{$listing->url}#collection",
                'name' => $listing->name,
                'url' => $listing->url,
                'mainEntity' => [
                    '@type' => 'ItemList',
                    '@id' => $itemListId,
                ],
            ],
            [
                '@type' => 'ItemList',
                '@id' => $itemListId,
                'numberOfItems' => count($itemListElements),
                'itemListElement' => $itemListElements,
            ],
        ];
    }

    /** Format seconds as an ISO 8601 duration, for example PT1H2M5S. */
    public static function isoDuration(int $seconds): string
    {
        $hours = intdiv($seconds, 3600);
        $minutes = intdiv($seconds % 3600, 60);
        $remainingSeconds = $seconds % 60;

        return "PT{$hours}H{$minutes}M{$remainingSeconds}S";
    }
}
